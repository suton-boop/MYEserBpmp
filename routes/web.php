<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\CertificateTemplateController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\PublicCertificateController;
use App\Http\Controllers\CertificateFlowController;

use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\RoleController as AdminRoleController;
use App\Http\Controllers\Admin\PermissionController as AdminPermissionController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\ApprovalController;
use App\Http\Controllers\Admin\MonitoringController;
use App\Http\Controllers\Admin\AuditController;

use App\Http\Controllers\Admin\Tte\TteDashboardController;
use App\Http\Controllers\Admin\Tte\SignerCertificateController;
use App\Http\Controllers\Admin\Tte\SigningController;

use App\Models\Certificate;

/*
|--------------------------------------------------------------------------
| PUBLIK
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => view('public.home'))->name('public.home');

Route::get('/verify', fn () => view('public.verify-form'))->name('public.verify.form');

Route::get('/verify/{code}', function (string $code) {
    $cert = Certificate::with(['event', 'participant'])
        ->where('verify_token', $code)
        ->firstOrFail();

    return view('public.verify-show', compact('cert'));
})
->where('code', '[A-Za-z0-9\-_]+')
->name('public.verify.show');

Route::get('/search', fn () => view('public.search'))->name('public.search');
Route::post('/search', [PublicCertificateController::class, 'search'])
    ->middleware('throttle:20,1')
    ->name('public.search.process');


/*
|--------------------------------------------------------------------------
| AUTH DEFAULT (Breeze)
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth'])->name('dashboard');


/*
|--------------------------------------------------------------------------
| ADMIN AREA
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['auth'])
    ->name('admin.')
    ->group(function () {

        // Dashboard Admin
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | PESERTA (admin.participants.*)
        |--------------------------------------------------------------------------
        */
        Route::prefix('participants')->name('participants.')->group(function () {
            Route::get('/', [ParticipantController::class, 'index'])->name('index');
            Route::get('/template.csv', [ParticipantController::class, 'templateCsv'])->name('template');

            Route::get('/import', [ParticipantController::class, 'importForm'])->name('import.form');
            Route::post('/import', [ParticipantController::class, 'importStore'])->name('import.store');

            Route::get('/create', [ParticipantController::class, 'create'])->name('create');
            Route::post('/', [ParticipantController::class, 'store'])->name('store');

            Route::get('/{participant}/edit', [ParticipantController::class, 'edit'])->whereNumber('participant')->name('edit');
            Route::patch('/{participant}', [ParticipantController::class, 'update'])->whereNumber('participant')->name('update');
            Route::delete('/{participant}', [ParticipantController::class, 'destroy'])->whereNumber('participant')->name('destroy');
        });

        /*
        |--------------------------------------------------------------------------
        | CERTIFICATES (admin.certificates.*)
        |--------------------------------------------------------------------------
        */
        Route::prefix('certificates')->name('certificates.')->group(function () {
            Route::get('/', [CertificateController::class, 'index'])->name('index');

            Route::post('/generate-all', [CertificateController::class, 'generateAll'])->name('generateAll');
            Route::post('/participants/{participant}/generate-one', [CertificateController::class, 'generateOne'])
                ->whereNumber('participant')->name('generateOne');

            Route::post('/{certificate}/submit', [CertificateFlowController::class, 'submit'])
                ->whereNumber('certificate')->name('submit');
            Route::post('/submit-all', [CertificateFlowController::class, 'submitAll'])->name('submitAll');

            Route::post('/{certificate}/generate-pdf', [CertificateController::class, 'generatePdfOne'])
                ->whereNumber('certificate')->name('generatePdfOne');
            Route::post('/generate-pdf-all', [CertificateController::class, 'generatePdfAll'])->name('generatePdfAll');

            Route::get('/{certificate}/view', [CertificateController::class, 'preview'])
                ->whereNumber('certificate')->name('view');
            Route::get('/{certificate}/download', [CertificateController::class, 'download'])
                ->whereNumber('certificate')->name('download');
        });

        /*
        |--------------------------------------------------------------------------
        | EMAILS (admin.emails.*)
        |--------------------------------------------------------------------------
        */
        Route::prefix('emails')->name('emails.')->group(function () {
            Route::get('/', [EmailController::class, 'index'])->name('index');
            Route::post('/send', [EmailController::class, 'send'])->name('send');
        });

        /*
        |--------------------------------------------------------------------------
        | REPORTS
        |--------------------------------------------------------------------------
        */
        Route::get('/reports', [ReportController::class, 'index'])->name('reports');

        /*
        |--------------------------------------------------------------------------
        | PROFILE (Breeze)
        |--------------------------------------------------------------------------
        */
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        /*
        |--------------------------------------------------------------------------
        | SYSTEM MANAGEMENT (admin.system.*)
        |--------------------------------------------------------------------------
        */
        Route::prefix('system')->name('system.')->group(function () {

            // Templates (admin.system.templates.*)
            Route::prefix('templates')->name('templates.')->group(function () {
                Route::get('/', [CertificateTemplateController::class, 'index'])->name('index');
                Route::get('/create', [CertificateTemplateController::class, 'create'])->name('create');
                Route::post('/', [CertificateTemplateController::class, 'store'])->name('store');

                Route::get('/{template}', [CertificateTemplateController::class, 'show'])->whereNumber('template')->name('show');
                Route::get('/{template}/edit', [CertificateTemplateController::class, 'edit'])->whereNumber('template')->name('edit');
                Route::patch('/{template}', [CertificateTemplateController::class, 'update'])->whereNumber('template')->name('update');
                Route::delete('/{template}', [CertificateTemplateController::class, 'destroy'])->whereNumber('template')->name('destroy');
                Route::patch('/{template}/toggle', [CertificateTemplateController::class, 'toggle'])->whereNumber('template')->name('toggle');
            });

            // Users (admin.system.users.*)
            Route::prefix('users')->name('users.')->group(function () {
                Route::get('/', [AdminUserController::class, 'index'])->name('index');
                Route::get('/create', [AdminUserController::class, 'create'])->name('create');
                Route::post('/', [AdminUserController::class, 'store'])->name('store');

                Route::get('/{user}/edit', [AdminUserController::class, 'edit'])->whereNumber('user')->name('edit');
                Route::patch('/{user}', [AdminUserController::class, 'update'])->whereNumber('user')->name('update');
                Route::delete('/{user}', [AdminUserController::class, 'destroy'])->whereNumber('user')->name('destroy');
            });

            // Roles (admin.system.roles.*)
            Route::prefix('roles')->name('roles.')->group(function () {
                Route::get('/', [AdminRoleController::class, 'index'])->name('index');
                Route::get('/{role}/edit', [AdminRoleController::class, 'edit'])->whereNumber('role')->name('edit');
                Route::patch('/{role}', [AdminRoleController::class, 'update'])->whereNumber('role')->name('update');
            });

            // Permissions
            Route::get('/permissions', [AdminPermissionController::class, 'index'])->name('permissions.index');

            // Events
            Route::resource('events', AdminEventController::class)->except(['show']);

            // Approvals (admin.system.approvals.*)
            Route::prefix('approvals')->name('approvals.')->group(function () {
                Route::get('/', [ApprovalController::class, 'index'])->name('index');
                Route::post('/{certificate}/approve', [ApprovalController::class, 'approve'])->whereNumber('certificate')->name('approve');
                Route::post('/{certificate}/reject', [ApprovalController::class, 'reject'])->whereNumber('certificate')->name('reject');

                Route::post('/approve-all', [ApprovalController::class, 'approveAll'])->name('approveAll');
                Route::post('/reject-all', [ApprovalController::class, 'rejectAll'])->name('rejectAll');
            });
        });

        /*
        |--------------------------------------------------------------------------
        | TTE (admin.tte.*) ✅ BERSIH: TIDAK ADA prefix admin/tte DI DALAMNYA
        |--------------------------------------------------------------------------
        */
        Route::prefix('tte')->name('tte.')->group(function () {

            // Dashboard
            Route::get('/', [TteDashboardController::class, 'index'])->name('index');

            /**
             * Signer Certificates
             */
            Route::get('/signer-certificates', [SignerCertificateController::class, 'index'])->name('signers.index');
            Route::get('/signer-certificates/create', [SignerCertificateController::class, 'create'])->name('signers.create');
            Route::post('/signer-certificates', [SignerCertificateController::class, 'store'])->name('signers.store');
            Route::post('/signer-certificates/{id}/activate', [SignerCertificateController::class, 'activate'])->name('signers.activate');
            Route::post('/signer-certificates/{id}/deactivate', [SignerCertificateController::class, 'deactivate'])->name('signers.deactivate');

            /**
             * Signing Queue
             */
            Route::get('/signing', [SigningController::class, 'index'])->name('signing.index');

            // Bulk dispatch (max 20)
            Route::post('/signing/dispatch-bulk', [SigningController::class, 'dispatchBulk'])->name('signing.dispatchBulk');

            // Single sign now
            Route::post('/signing/{id}/sign-now', [SigningController::class, 'signNow'])->name('signing.signNow');

            // OPTIONAL: jika kamu masih ingin endpoint dispatchSingle (selain signNow)
            // Route::post('/signing/{certificate}/dispatch', [SigningController::class, 'dispatchSingle'])->name('signing.dispatchSingle');

    Route::prefix('admin')->middleware(['auth'])->name('admin.')->group(function () {
    Route::prefix('tte')->name('tte.')->group(function () {
    Route::get('/signing', [SigningController::class, 'index'])->name('signing.index');

    Route::post('/signing/dispatch-bulk', [SigningController::class, 'dispatchBulk'])->name('signing.dispatchBulk');
    Route::post('/signing/{id}/dispatch', [SigningController::class, 'dispatchSingle'])->name('signing.dispatchSingle');

    Route::get('/signing/{id}/preview', [SigningController::class, 'preview'])->name('signing.preview');

    Route::post('/signing/{id}/sign-now', [SigningController::class, 'signNow'])->name('signing.signNow');
  });
});

        });

        /*
        |--------------------------------------------------------------------------
        | Monitoring & Audit
        |--------------------------------------------------------------------------
        */
        Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
        Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
    });

require __DIR__ . '/auth.php';