<?php

namespace App\Http\Controllers\Admin\Tte;

use App\Http\Controllers\Controller;
use App\Domain\Certificates\Models\Certificate;
use App\Domain\Certificates\Models\DigitalSignature;
use App\Domain\Certificates\Models\SignerCertificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class TteDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Optional: tambah role middleware sesuai kebutuhan
        // $this->middleware(['role:admin|superadmin']);
    }

    public function index(Request $request)
    {
        // Defensive: jangan crash kalau tabel belum ada (saat setup dev)
        $signedToday = Schema::hasTable('digital_signatures')
            ? DigitalSignature::query()->whereDate('signed_at', now()->toDateString())->count()
            : 0;

        $pendingApproved = Schema::hasTable('certificates')
            ? Certificate::query()->where('status', 'approved')->count()
            : 0;

        $activeSigners = Schema::hasTable('signer_certificates')
            ? SignerCertificate::query()->where('is_active', true)->count()
            : 0;

        return view('admin.tte.dashboard', compact('signedToday', 'pendingApproved', 'activeSigners'));
    }
}