<?php

namespace App\Http\Controllers\Admin\Tte;

use App\Http\Controllers\Controller;
use App\Models\SignerCertificate;
use Illuminate\Http\Request;

class SignerCertificateController extends Controller
{
    public function index(Request $request)
    {
        // Dropdown signer / list signer
        $signers = SignerCertificate::query()
            ->orderBy('name')
            ->get(['id','name','code','is_active']); // sesuaikan jika kolom beda

        // Kalau view kamu juga butuh list untuk tabel, pakai ini juga:
        // $items = SignerCertificate::query()->latest()->paginate(20);

        return view('admin.tte.signers.index', compact('signers'));
        // kalau butuh tabel: return view('admin.tte.signers.index', compact('signers','items'));
    }
}




