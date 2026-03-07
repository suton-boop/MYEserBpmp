@extends('public.layout')

@section('title', 'Beranda - E-Sertifikat BPMP Kaltim')

@section('content')
<!-- Hero Section -->
<div class="row align-items-center g-5 py-5">
    <div class="col-lg-6">
        <div class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold mb-3 shadow-none border border-primary border-opacity-10">
            <i class="fa-solid fa-check-double me-1"></i> RESMI & TERVERIFIKASI
        </div>
        <h1 class="display-5 fw-bold text-dark mb-3 lh-sm">Sistem Layanan <span style="color: var(--nav-blue-2);">E-Sertifikat</span> Terpadu</h1>
        <p class="lead text-muted mb-4 fs-6">
            Akses mandiri untuk pencarian, unduhan, dan verifikasi sertifikat kegiatan di BPMP Provinsi Kalimantan Timur secara digital, instan, dan aman dengan Tanda Tangan Elektronik (TTE) terverifikasi.
        </p>
        
        <div class="d-flex flex-wrap gap-3">
            <a href="{{ route('public.search') }}" class="btn btn-primary btn-lg px-4 fw-bold shadow-sm d-flex align-items-center gap-2 rounded-3" style="background: var(--nav-blue); border-color: var(--nav-blue);">
                <i class="fa-solid fa-magnifying-glass"></i> Cari Sertifikat
            </a>
            <a href="{{ route('public.verify.form') }}" class="btn btn-outline-dark btn-lg px-4 fw-bold d-flex align-items-center gap-2 rounded-3">
                <i class="fa-solid fa-qrcode"></i> Verifikasi TTE
            </a>
        </div>
    </div>
    
    <div class="col-lg-6 text-center">
        <div class="position-relative">
            <div class="p-5 d-flex justify-content-center">
                 <div class="hero-circle-wrap">
                    <div class="hero-circle-bg"></div>
                    <i class="fa-solid fa-file-shield" style="font-size: 10rem; color: var(--nav-blue); position: relative; z-index: 2;"></i>
                 </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Section -->
<div class="row g-4 mb-5 pb-5 mt-2">
    <div class="col-md-6">
        <div class="card border border-light shadow-sm rounded-4 p-4 text-center overflow-hidden position-relative stat-card bg-white">
            <div class="position-relative z-1">
                <div class="display-4 mb-1 fw-bold" style="color: var(--nav-blue);">{{ number_format($stats['total_certificates'], 0, ',', '.') }}</div>
                <div class="text-dark fw-bold fs-5">Sertifikat Diterbitkan</div>
                <div class="text-muted small mt-1">Total sertifikat yang telah melewati proses digital</div>
            </div>
            <div class="stat-icon-bg">
                <i class="fa-solid fa-certificate"></i>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border border-light shadow-sm rounded-4 p-4 text-center overflow-hidden position-relative stat-card bg-white">
            <div class="position-relative z-1">
                <div class="display-4 mb-1 fw-bold" style="color: var(--nav-blue-2);">{{ number_format($stats['total_participants'], 0, ',', '.') }}</div>
                <div class="text-dark fw-bold fs-5">Peserta Terdaftar</div>
                <div class="text-muted small mt-1">Peserta aktif yang terdata dalam sistem</div>
            </div>
            <div class="stat-icon-bg">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="row g-4 pt-5 border-top border-light">
    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm rounded-4 text-center p-4 bg-white hover-up-card">
            <div class="d-inline-flex bg-primary bg-opacity-10 text-primary rounded-circle mb-4 align-items-center justify-content-center mx-auto feature-icon-wrap" style="color: var(--nav-blue) !important;">
                <i class="fa-regular fa-clock"></i>
            </div>
            <h5 class="fw-bold text-dark mb-3">Akses Instan</h5>
            <p class="text-muted small mb-0 px-2">Sertifikat tersedia seketika setelah disetujui, tanpa perlu menunggu pengiriman dokumen fisik.</p>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm rounded-4 text-center p-4 bg-white hover-up-card">
            <div class="d-inline-flex bg-success bg-opacity-10 text-success rounded-circle mb-4 align-items-center justify-content-center mx-auto feature-icon-wrap">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <h5 class="fw-bold text-dark mb-3">Legal & Aman</h5>
            <p class="text-muted small mb-0 px-2">Keaslian dokumen terjamin dengan TTE (Tanda Tangan Elektronik) resmi sesuai standar BSrE.</p>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm rounded-4 text-center p-4 bg-white hover-up-card">
            <div class="d-inline-flex bg-danger bg-opacity-10 text-danger rounded-circle mb-4 align-items-center justify-content-center mx-auto feature-icon-wrap">
                <i class="fa-solid fa-qrcode"></i>
            </div>
            <h5 class="fw-bold text-dark mb-3">Verifikasi Mandiri</h5>
            <p class="text-muted small mb-0 px-2">Mudah memvalidasi keaslian sertifikat hanya dengan scan QR Code atau input nomor sertifikat.</p>
        </div>
    </div>
</div>

<style>
    .stat-card {
        transition: all 0.3s ease;
        border: 1px solid #f0f0f0 !important;
    }
    .stat-card:hover {
        border-color: var(--nav-blue-2) !important;
        transform: translateY(-5px);
    }
    .stat-icon-bg {
        position: absolute;
        right: -15px;
        bottom: -15px;
        font-size: 8rem;
        opacity: 0.04;
        color: var(--nav-blue);
        transform: rotate(-10deg);
        z-index: 0;
    }
    .hover-up-card {
         transition: all 0.3s ease;
    }
    .hover-up-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.06) !important;
    }
    .feature-icon-wrap {
        width: 72px;
        height: 72px;
        font-size: 28px;
    }
    .hero-circle-wrap {
        position: relative;
        width: 300px;
        height: 300px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .hero-circle-bg {
        position: absolute;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle, rgba(13, 79, 139, 0.08) 0%, transparent 70%);
        border-radius: 50%;
        animation: pulse 4s infinite;
    }
    @keyframes pulse {
        0% { transform: scale(1); opacity: 0.5; }
        50% { transform: scale(1.1); opacity: 0.8; }
        100% { transform: scale(1); opacity: 0.5; }
    }
</style>
@endsection
