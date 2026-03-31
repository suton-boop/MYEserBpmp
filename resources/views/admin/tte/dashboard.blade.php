@extends('layouts.app')
@section('title','Dashboard TTE')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Dashboard TTE</h4>
        <div class="text-muted">Ringkasan status modul Tanda Tangan Elektronik.</div>
    </div>
    <div class="d-flex gap-2">
        <a class="btn btn-outline-primary shadow-sm" href="{{ route('admin.tte.signing.index') }}">
            <i class="fa-solid fa-signature me-1"></i> Antrean Signing
        </a>
        <a class="btn btn-outline-secondary shadow-sm" href="{{ route('admin.tte.signers.index') }}">
            <i class="fa-solid fa-key me-1"></i> Data Signer
        </a>
    </div>
</div>

{{-- KPI CARDS --}}
<div class="row g-4 mb-4">
    <!-- Antrean TTE -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden" style="background: linear-gradient(135deg, #FF9A9E 0%, #FECFEF 100%);">
            <div class="card-body position-relative z-1 text-white p-4">
                <i class="fa-solid fa-layer-group position-absolute end-0 bottom-0 text-white opacity-25 me-3 mb-3" style="font-size: 5rem;"></i>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0 text-white shadow-sm px-3 py-1 bg-white bg-opacity-25 rounded-pill d-inline-block">Dalam Antrean (Approved)</h6>
                </div>
                <h2 class="display-4 fw-bolder mb-0 text-white drop-shadow">{{ number_format($pendingApproved ?? 0) }}</h2>
                <div class="mt-2 text-white opacity-75 small">Sertifikat menunggu ditandatangani</div>
            </div>
            <div class="card-footer bg-white bg-opacity-25 border-0 px-4 py-3">
                <a href="{{ route('admin.tte.signing.index') }}" class="text-white text-decoration-none fw-semibold d-flex justify-content-between align-items-center">
                    Eksekusi Antrean <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- TTE Berhasil Hari Ini -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden" style="background: linear-gradient(135deg, #12c2e9 0%, #c471ed 50%, #f64f59 100%);">
            <div class="card-body position-relative z-1 text-white p-4">
                <i class="fa-solid fa-certificate position-absolute end-0 bottom-0 text-white opacity-25 me-3 mb-3" style="font-size: 5rem;"></i>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0 text-white shadow-sm px-3 py-1 bg-white bg-opacity-25 rounded-pill d-inline-block">TTE Selesai (Hari ini)</h6>
                </div>
                <h2 class="display-4 fw-bolder mb-0 text-white drop-shadow">{{ number_format($signedToday ?? 0) }}</h2>
                <div class="mt-2 text-white opacity-75 small">Dokumen valid diparaf hari ini</div>
            </div>
        </div>
    </div>

    <!-- Signer Aktif -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden position-relative bg-white">
            <div class="card-body p-4 d-flex flex-column justify-content-center">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="bg-success text-white px-3 py-2 rounded-3 d-inline-flex justify-content-center align-items-center shadow-sm">
                        <i class="fa-solid fa-user-check fs-4"></i>
                    </div>
                </div>
                <div>
                    <h2 class="display-5 fw-bolder mb-1 text-dark">{{ number_format($activeSigners ?? 0) }}</h2>
                    <h6 class="fw-semibold text-muted mb-0">Signer (Akun TTE) Aktif</h6>
                </div>
                <div class="mt-3">
                    <a href="{{ route('admin.tte.signers.index') }}" class="btn btn-sm btn-light border-secondary border-opacity-25 rounded-pill fw-semibold text-primary">Kelola Signer <i class="fa-solid fa-chevron-right ms-1" style="font-size: 10px;"></i></a>
                </div>
                <i class="fa-solid fa-shield-halved position-absolute text-success opacity-10" style="font-size: 8rem; right: -20px; bottom: -20px;"></i>
            </div>
        </div>
    </div>

    <!-- TTE Terjadwal -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden position-relative bg-white">
            <div class="card-body p-4 d-flex flex-column justify-content-center">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="bg-info text-white px-3 py-2 rounded-3 d-inline-flex justify-content-center align-items-center shadow-sm">
                        <i class="fa-solid fa-calendar-check fs-4"></i>
                    </div>
                </div>
                <div>
                    <h2 class="display-5 fw-bolder mb-1 text-dark">{{ number_format($scheduledCount ?? 0) }}</h2>
                    <h6 class="fw-semibold text-muted mb-0">TTE Terjadwal</h6>
                </div>
                <div class="mt-3">
                    <a href="{{ route('admin.tte.signing.index') }}?status=scheduled" class="btn btn-sm btn-light border-secondary border-opacity-25 rounded-pill fw-semibold text-info">Lihat Jadwal <i class="fa-solid fa-chevron-right ms-1" style="font-size: 10px;"></i></a>
                </div>
                <i class="fa-solid fa-clock position-absolute text-info opacity-10" style="font-size: 8rem; right: -20px; bottom: -20px;"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom p-3">
                <h6 class="mb-0 fw-bold d-flex align-items-center">
                   <div class="bg-primary bg-opacity-10 text-primary rounded d-inline-flex justify-content-center align-items-center p-2 me-2">
                       <i class="fa-solid fa-info-circle"></i>
                   </div>
                   Status Sistem TTE Terkini
                </h6>
            </div>
            <div class="card-body p-4">
               @if(isset($pendingApproved) && $pendingApproved > 0)
                   <div class="alert alert-warning border-warning border-opacity-50 bg-warning bg-opacity-10 rounded-4 d-flex align-items-center p-3 mb-0">
                       <i class="fa-solid fa-triangle-exclamation text-warning fs-3 me-3"></i>
                       <div>
                           <h6 class="fw-bold text-dark mb-1">Terdapat {{ $pendingApproved }} Dokumen Mengantre!</h6>
                           <p class="mb-0 small text-muted">Aplikasi mendeteksi ada sertifikat berstatus <b>APPROVED / FINAL_GENERATED</b> yang tertahan dan belum dibubuhi stempel elektronik. Harap segera menuju menu <b>Signing Queue</b> untuk melanjutkan Dispatch Sign massal.</p>
                       </div>
                   </div>
               @else
                   <div class="alert alert-success border-success border-opacity-50 bg-success bg-opacity-10 rounded-4 d-flex align-items-center p-3 mb-0">
                       <i class="fa-solid fa-circle-check text-success fs-3 me-3"></i>
                       <div>
                           <h6 class="fw-bold text-dark mb-1">Semua Bersih! Antrean Kosong</h6>
                           <p class="mb-0 small text-muted">Aplikasi tidak mendeteksi adanya antrean PDF yang menunggu untuk dieksekusi stempel elektronik.</p>
                       </div>
                   </div>
               @endif
            </div>
        </div>
    </div>
</div>

<style>
.drop-shadow {
    text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
}
</style>
@endsection