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
use App\Http\Controllers\Admin\VisitorController;

use App\Models\Certificate;

/* |------------------------- | PUBLIK |------------------------- */
Route::get('/', function () {
    $stats = [
        'total_certificates' => \App\Models\Certificate::whereIn('status', [
            \App\Models\Certificate::STATUS_SIGNED,
            \App\Models\Certificate::STATUS_SENT,
            \App\Models\Certificate::STATUS_FINAL_GENERATED
        ])->count(),
        'total_participants' => \App\Models\Participant::count(),
        'total_visitors' => \App\Models\Visitor::count(),
    ];
    return view('public.home', compact('stats'));
})->name('public.home');

Route::get('/verify', [PublicCertificateController::class , 'verifyForm'])->name('public.verify.form');
Route::post('/verify', [PublicCertificateController::class , 'verifyByNumber'])->name('public.verify.process');
Route::get('/verify/{code}', [PublicCertificateController::class , 'verifyByToken'])->where('code', '[A-Za-z0-9\-_]+')->name('public.verify.show');

Route::get('/search', fn() => view('public.search'))->name('public.search');
Route::post('/search', [PublicCertificateController::class , 'search'])
    ->middleware('throttle:20,1')
    ->name('public.search.process');
Route::get('/download/{code}', [PublicCertificateController::class , 'download'])
    ->where('code', '[A-Za-z0-9\-_]+')
    ->name('public.download');

/* |------------------------- | AUTH DEFAULT |------------------------- */
Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth'])->name('dashboard');

/* |------------------------- | ADMIN |------------------------- */
Route::prefix('admin')
    ->middleware(['auth'])
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class , 'index'])->name('dashboard');

        // participants
        Route::prefix('participants')->name('participants.')->group(function () {
            Route::get('/', [ParticipantController::class , 'index'])->name('index');
            Route::get('/duplicates', [ParticipantController::class , 'duplicates'])->name('duplicates');
            Route::get('/template.xls', [ParticipantController::class , 'templateExcel'])->name('template');
            Route::get('/import', [ParticipantController::class , 'importForm'])->name('import.form');
            Route::post('/import', [ParticipantController::class , 'importStore'])->name('import.store');
            Route::get('/create', [ParticipantController::class , 'create'])->name('create');
            Route::post('/', [ParticipantController::class , 'store'])->name('store');
            Route::get('/{participant}/edit', [ParticipantController::class , 'edit'])->whereNumber('participant')->name('edit');
            Route::patch('/{participant}', [ParticipantController::class , 'update'])->whereNumber('participant')->name('update');
            Route::delete('/{participant}', [ParticipantController::class , 'destroy'])->whereNumber('participant')->name('destroy');
        }
        );

        // certificates
        Route::prefix('certificates')->name('certificates.')->group(function () {
            Route::get('/', [CertificateController::class , 'index'])->name('index');
            Route::get('/published', [CertificateController::class , 'published'])->name('published');
            Route::get('/published/export', [CertificateController::class , 'exportPublished'])->name('published.export');

            Route::post('/generate-all', [CertificateController::class , 'generateAll'])->name('generateAll');
            Route::post('/participants/{participant}/generate-one', [CertificateController::class , 'generateOne'])
                ->whereNumber('participant')->name('generateOne');

            Route::post('/{certificate}/submit', [CertificateFlowController::class , 'submit'])
                ->whereNumber('certificate')->name('submit');
            Route::post('/submit-all', [CertificateFlowController::class , 'submitAll'])->name('submitAll');

            Route::post('/{certificate}/generate-pdf', [CertificateController::class , 'generatePdfOne'])
                ->whereNumber('certificate')->name('generatePdfOne');
            Route::post('/generate-pdf-all', [CertificateController::class , 'generatePdfAll'])->name('generatePdfAll');

            Route::get('/{certificate}/view', [CertificateController::class , 'preview'])
                ->whereNumber('certificate')->name('view');
            Route::get('/{certificate}/download', [CertificateController::class , 'download'])
                ->whereNumber('certificate')->name('download');
            Route::post('/{certificate}/revise', [CertificateFlowController::class , 'revise'])
                ->whereNumber('certificate')->name('revise');
        }
        );

        // emails
        Route::prefix('emails')->name('emails.')->group(function () {
            Route::get('/', [EmailController::class , 'index'])->name('index');
            Route::post('/send', [EmailController::class , 'send'])->name('send');
        }
        );

        Route::get('/reports', [ReportController::class , 'index'])->name('reports');

        // profile
        Route::get('/profile', [ProfileController::class , 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class , 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class , 'destroy'])->name('profile.destroy');

        // system management
        Route::prefix('system')->name('system.')->group(function () {
            Route::prefix('templates')->name('templates.')->group(function () {
                    Route::get('/', [CertificateTemplateController::class , 'index'])->name('index');
                    Route::get('/create', [CertificateTemplateController::class , 'create'])->name('create');
                    Route::post('/', [CertificateTemplateController::class , 'store'])->name('store');
                    Route::get('/{template}', [CertificateTemplateController::class , 'show'])->whereNumber('template')->name('show');
                    Route::get('/{template}/edit', [CertificateTemplateController::class , 'edit'])->whereNumber('template')->name('edit');
                    Route::patch('/{template}', [CertificateTemplateController::class , 'update'])->whereNumber('template')->name('update');
                    Route::delete('/{template}', [CertificateTemplateController::class , 'destroy'])->whereNumber('template')->name('destroy');
                    Route::patch('/{template}/toggle', [CertificateTemplateController::class , 'toggle'])->whereNumber('template')->name('toggle');
                }
                );

                Route::prefix('users')->name('users.')->group(function () {
                    Route::get('/', [AdminUserController::class , 'index'])->name('index');
                    Route::get('/import', [AdminUserController::class , 'importForm'])->name('import.form');
                    Route::post('/import', [AdminUserController::class , 'importStore'])->name('import.store');
                    Route::get('/create', [AdminUserController::class , 'create'])->name('create');
                    Route::post('/', [AdminUserController::class , 'store'])->name('store');
                    Route::get('/{user}/edit', [AdminUserController::class , 'edit'])->whereNumber('user')->name('edit');
                    Route::patch('/{user}', [AdminUserController::class , 'update'])->whereNumber('user')->name('update');
                    Route::delete('/{user}', [AdminUserController::class , 'destroy'])->whereNumber('user')->name('destroy');
                }
                );

                Route::prefix('roles')->name('roles.')->group(function () {
                    Route::get('/', [AdminRoleController::class , 'index'])->name('index');
                    Route::get('/{role}/edit', [AdminRoleController::class , 'edit'])->whereNumber('role')->name('edit');
                    Route::patch('/{role}', [AdminRoleController::class , 'update'])->whereNumber('role')->name('update');
                }
                );

                Route::get('/permissions', [AdminPermissionController::class , 'index'])->name('permissions.index');

                Route::get('/events/{event}/download-signed', [AdminEventController::class , 'downloadSigned'])->name('events.downloadSigned');
                Route::resource('events', AdminEventController::class)->except(['show']);

                Route::prefix('approvals')->name('approvals.')->group(function () {
                    Route::get('/', [ApprovalController::class , 'index'])->name('index');
                    Route::post('/{certificate}/approve', [ApprovalController::class , 'approve'])->whereNumber('certificate')->name('approve');
                    Route::post('/{certificate}/reject', [ApprovalController::class , 'reject'])->whereNumber('certificate')->name('reject');
                    Route::post('/approve-all', [ApprovalController::class , 'approveAll'])->name('approveAll');
                    Route::post('/reject-all', [ApprovalController::class , 'rejectAll'])->name('rejectAll');
                }
                );
            }
            );

            /*
         |-------------------------
         | TTE (admin/tte/*)
         |-------------------------
         */
            Route::prefix('tte')->name('tte.')->group(function () {

            Route::get('/', [TteDashboardController::class , 'index'])->name('index');

            Route::get('/signer-certificates', [SignerCertificateController::class , 'index'])->name('signers.index');
            Route::get('/signer-certificates/create', [SignerCertificateController::class , 'create'])->name('signers.create');
            Route::post('/signer-certificates', [SignerCertificateController::class , 'store'])->name('signers.store');
            Route::post('/signer-certificates/{id}/activate', [SignerCertificateController::class , 'activate'])->name('signers.activate');
            Route::post('/signer-certificates/{id}/deactivate', [SignerCertificateController::class , 'deactivate'])->name('signers.deactivate');

            Route::get('/signing', [SigningController::class , 'index'])->name('signing.index');
            Route::get('/signing/{id}/preview', [SigningController::class , 'preview'])->whereNumber('id')->name('signing.preview');

            Route::post('/signing/dispatch-bulk', [SigningController::class , 'dispatchBulk'])->name('signing.dispatchBulk');
            Route::post('/signing/{id}/dispatch', [SigningController::class , 'dispatchSingle'])->whereNumber('id')->name('signing.dispatchSingle');

            // alias optional
            Route::post('/signing/{id}/sign-now', [SigningController::class , 'signNow'])->whereNumber('id')->name('signing.signNow');
        }
        );

        Route::get('/monitoring', [MonitoringController::class , 'index'])->name('monitoring.index');
        Route::post('/monitoring/retry-failed', [MonitoringController::class , 'retryAllFailed'])->name('monitoring.retryFailed');
        Route::post('/monitoring/clear-failed', [MonitoringController::class , 'clearAllFailed'])->name('monitoring.clearFailed');
        Route::get('/audit', [AuditController::class , 'index'])->name('audit.index');
        Route::get('/visitors', [VisitorController::class , 'index'])->name('visitors.index');
    });

Route::get('/fix-storage', function () {
    $target = storage_path('app/public');
    $link = public_path('storage');
    if (file_exists($link)) {
        return 'Link "storage" sudah ada.';
    }
    symlink($target, $link);
    return 'Link "storage" berhasil dibuat.';
});

require __DIR__ . '/auth.php';