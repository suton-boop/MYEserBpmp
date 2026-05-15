@extends('layouts.app')

@section('title', 'Detail Template Sertifikat')

@section('content')
@php
  // URL background (public storage)
  $bgUrl = $template->file_path ? asset('storage/'.$template->file_path) : null;

  // Tentukan extension untuk preview
  $ext = $template->file_path
      ? strtolower(pathinfo($template->file_path, PATHINFO_EXTENSION))
      : null;

  // Settings aman: bisa string JSON atau array
  $settings = $template->settings;
  if (is_string($settings)) {
      $decoded = json_decode($settings, true);
      $settings = is_array($decoded) ? $decoded : [];
  } elseif (!is_array($settings)) {
      $settings = [];
  }
@endphp

<div class="row">
    <div class="col-lg-12">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-dark mb-1">Detail Template Sertifikat</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item small"><a href="{{ route('admin.system.templates.index') }}" class="text-muted text-decoration-none">Template</a></li>
                        <li class="breadcrumb-item small active text-primary fw-semibold" aria-current="page">{{ $template->code }}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.system.templates.edit', $template) }}" class="btn btn-warning rounded-pill px-4 shadow-sm fw-semibold">
                    <i class="fa-solid fa-pen-to-square me-1"></i> Edit Template
                </a>
                <a href="{{ route('admin.system.templates.index') }}" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm bg-white">
                    <i class="fa-solid fa-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left Column: Info & Details -->
            <div class="col-xl-4 col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                    <div class="card-header bg-primary bg-gradient-premium text-white p-4 border-0">
                        <h6 class="mb-0 fw-bold"><i class="fa-solid fa-circle-info me-2"></i>Informasi Umum</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <label class="text-muted small fw-bold text-uppercase ls-1 d-block mb-1">Nama Template</label>
                            <div class="fw-bold text-dark fs-5 line-height-sm">{{ $template->name }}</div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-6">
                                <label class="text-muted small fw-bold text-uppercase ls-1 d-block mb-1">Kode Unik</label>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-3 py-2 rounded-pill">
                                    {{ $template->code }}
                                </span>
                            </div>
                            <div class="col-6">
                                <label class="text-muted small fw-bold text-uppercase ls-1 d-block mb-1">Status</label>
                                @if($template->is_active)
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill">
                                        <i class="fa-solid fa-circle-check me-1"></i> Aktif
                                    </span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 rounded-pill">
                                        <i class="fa-solid fa-circle-xmark me-1"></i> Nonaktif
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="text-muted small fw-bold text-uppercase ls-1 d-block mb-1">Deskripsi</label>
                            <div class="p-3 bg-light rounded-3 text-muted border-0" style="font-size: 0.9rem; min-height: 80px;">
                                {{ $template->description ?: 'Tidak ada deskripsi tambahan.' }}
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="text-muted small fw-bold text-uppercase ls-1 d-block mb-2">File Background</label>
                            @if($template->file_path)
                                <div class="p-3 border rounded-4 bg-white shadow-sm">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-2 me-3">
                                            <i class="fa-solid fa-file-image fs-4"></i>
                                        </div>
                                        <div class="overflow-hidden">
                                            <div class="text-dark fw-semibold text-truncate small">{{ basename($template->file_path) }}</div>
                                            <div class="text-muted x-small uppercase fw-bold ls-1">{{ strtoupper($ext) }} File</div>
                                        </div>
                                    </div>
                                    <div class="d-grid gap-2">
                                        <a href="{{ $bgUrl }}" target="_blank" class="btn btn-soft-primary btn-sm rounded-pill fw-bold py-2">
                                            <i class="fa-solid fa-eye me-1"></i> Lihat Fullscreen
                                        </a>
                                        <a href="{{ $bgUrl }}" download class="btn btn-outline-light btn-sm rounded-pill text-dark border fw-bold py-2 bg-white shadow-none">
                                            <i class="fa-solid fa-download me-1"></i> Unduh File
                                        </a>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning border-0 rounded-4 px-3 py-2 mt-1 small">
                                    <i class="fa-solid fa-triangle-exclamation me-2"></i> Belum ada file background diupload.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Preview & Settings -->
            <div class="col-xl-8 col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                    <div class="card-header bg-primary bg-gradient-premium text-white p-4 border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold"><i class="fa-solid fa-image me-2"></i>Visual Preview</h6>
                            <span class="badge bg-white bg-opacity-20 backdrop-blur rounded-pill fw-normal small px-3">Live Preview</span>
                        </div>
                    </div>
                    <div class="card-body p-4 bg-light bg-opacity-50">
                        @if($template->file_path)
                            <div class="bg-white p-2 rounded-4 shadow-sm border">
                                @if(in_array($ext, ['png','jpg','jpeg','webp']))
                                    <div class="preview-container position-relative overflow-hidden rounded-3">
                                        <img src="{{ $bgUrl }}" class="img-fluid d-block mx-auto" alt="Background Template">
                                    </div>
                                @elseif($ext === 'pdf')
                                    <iframe src="{{ $bgUrl }}" style="width:100%; height:520px;" class="border-0 rounded-3"></iframe>
                                @else
                                    <div class="p-5 text-center">
                                        <i class="fa-solid fa-file-circle-question text-muted mb-3" style="font-size: 4rem;"></i>
                                        <h6 class="text-muted">Format <b>.{{ $ext }}</b> tidak mendukung preview langsung.</h6>
                                        <a href="{{ $bgUrl }}" target="_blank" class="btn btn-primary rounded-pill px-4 mt-2">Buka File</a>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="text-center py-5 bg-white rounded-4 border">
                                <i class="fa-solid fa-mountain-sun text-muted opacity-25 mb-3" style="font-size: 5rem;"></i>
                                <h5 class="text-muted fw-bold">Gambar Tidak Tersedia</h5>
                                <p class="text-muted small">Silakan upload background di menu edit untuk melihat preview.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text-muted text-uppercase ls-1">Konfigurasi Fields (JSON)</h6>
                        <span class="badge bg-primary rounded-pill px-3">{{ count($settings['fields'] ?? []) }} Fields</span>
                    </div>
                    <div class="card-body p-0">
                        <pre class="bg-dark text-success p-4 mb-0 mono" style="font-size: 0.85rem; max-height: 400px; overflow-y: auto;">{{ json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-gradient-premium { background: linear-gradient(135deg, #2193b0 0%, #6dd5ed 100%) !important; }
    .ls-1 { letter-spacing: 1px; }
    .line-height-sm { line-height: 1.25; }
    .backdrop-blur { backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); }
    .btn-soft-primary { background-color: rgba(13, 110, 253, 0.1); color: #0d6efd; border: transparent; }
    .btn-soft-primary:hover { background-color: #0d6efd; color: #fff; }
    .preview-container { min-height: 300px; background-image: 
        linear-gradient(45deg, #f8f9fa 25%, transparent 25%), 
        linear-gradient(-45deg, #f8f9fa 25%, transparent 25%), 
        linear-gradient(45deg, transparent 75%, #f8f9fa 75%), 
        linear-gradient(-45deg, transparent 75%, #f8f9fa 75%);
        background-size: 20px 20px;
        background-position: 0 0, 0 10px, 10px -10px, -10px 0px; }
    .preview-container img { box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: 4px solid #fff; }
    pre::-webkit-scrollbar { width: 8px; }
    pre::-webkit-scrollbar-track { background: #1a1a1a; }
    pre::-webkit-scrollbar-thumb { background: #333; border-radius: 10px; }
    pre::-webkit-scrollbar-thumb:hover { background: #444; }
</style>
@endsection