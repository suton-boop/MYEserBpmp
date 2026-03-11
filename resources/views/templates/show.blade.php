@extends('layouts.app')

@section('title','View Template')

@section('content')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
  <div>
    <h4 class="mb-0">Detail Template</h4>
    <div class="text-muted">Preview background dan settings template.</div>
  </div>

  <div class="d-flex gap-2">
    <a href="{{ route('admin.system.templates.edit', $template) }}" class="btn btn-warning rounded-3">
      <i class="fa-solid fa-pen-to-square me-1"></i> Edit
    </a>

    <a href="{{ route('admin.system.templates.index') }}" class="btn btn-outline-secondary rounded-3">
      <i class="fa-solid fa-arrow-left me-1"></i> Kembali
    </a>
  </div>
</div>

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

<div class="card border-0 shadow-sm rounded-4">
  <div class="card-body">
    <div class="row g-4">

      {{-- KIRI: INFO --}}
      <div class="col-md-6">
        <div class="mb-2 text-muted">Nama</div>
        <div class="fw-semibold fs-5">{{ $template->name }}</div>

        <div class="mt-3 mb-2 text-muted">Kode</div>
        <div><span class="badge bg-secondary">{{ $template->code }}</span></div>

        <div class="mt-3 mb-2 text-muted">Status</div>
        @if($template->is_active)
          <span class="badge bg-success">Active</span>
        @else
          <span class="badge bg-danger">Nonaktif</span>
        @endif

        {{-- DESCRIPTION (opsional) --}}
        <div class="mt-4 mb-2 text-muted">Deskripsi</div>
        <div class="text-body">
          {{ $template->description ?: '-' }}
        </div>

        <div class="mt-4 mb-2 text-muted">File Background</div>

        @if($template->file_path)
          <div class="d-flex flex-column gap-1">
            <a href="{{ $bgUrl }}" target="_blank" class="text-decoration-none">
              {{ $template->file_path }}
            </a>
            <div class="d-flex gap-2">
              <a href="{{ $bgUrl }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-3">
                <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Buka
              </a>
              <a href="{{ $bgUrl }}" download class="btn btn-sm btn-outline-secondary rounded-3">
                <i class="fa-solid fa-download me-1"></i> Download
              </a>
            </div>
          </div>
        @else
          <div class="text-muted">Tidak ada file background.</div>
        @endif
      </div>

      {{-- KANAN: PREVIEW --}}
      <div class="col-md-6">
        <div class="mb-2 text-muted">Preview Background</div>

        @if($template->file_path)
          @if(in_array($ext, ['png','jpg','jpeg','webp']))
            <img src="{{ $bgUrl }}"
                 class="img-fluid border rounded-3"
                 alt="Background Template">
          @elseif($ext === 'pdf')
            <iframe src="{{ $bgUrl }}"
                    style="width:100%; height:520px;"
                    class="border rounded-3"></iframe>
            <div class="small text-muted mt-2">
              Jika PDF tidak tampil, <a href="{{ $bgUrl }}" target="_blank">klik untuk membuka</a>.
            </div>
          @else
            <div class="alert alert-info mb-0">
              Preview tidak tersedia untuk format <b>.{{ $ext }}</b>.
              <div class="mt-2">
                <a href="{{ $bgUrl }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-3">
                  Buka File
                </a>
              </div>
            </div>
          @endif
        @else
          <div class="alert alert-warning mb-0">
            Background belum diupload.
          </div>
        @endif
      </div>

      {{-- SETTINGS --}}
      <div class="col-12">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
          <div class="text-muted">Settings (JSON)</div>

          @if(!empty($settings))
            <span class="badge bg-light text-dark border">
              fields: {{ isset($settings['fields']) && is_array($settings['fields']) ? count($settings['fields']) : 0 }}
            </span>
          @endif
        </div>

        <pre class="bg-light border rounded-3 p-3 mb-0" style="white-space: pre-wrap;">{{ json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>

        @if(empty($settings))
          <div class="small text-muted mt-2">
            Settings masih kosong. Kamu bisa isi di menu Edit Template.
          </div>
        @endif
      </div>

    </div>
  </div>
</div>
@endsection