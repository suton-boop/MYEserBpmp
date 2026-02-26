@extends('layouts.app')
@section('title','Tambah Template')

@section('content')
  @include('templates.form', ['mode' => 'create'])
@endsection

@extends('layouts.app')

@section('title', 'Tambah Template')

@section('content')
<div class="container-fluid">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h4 class="mb-0">Tambah Template Sertifikat</h4>
      <div class="text-muted small">Upload file template sertifikat dan isi informasi template.</div>
    </div>

    <a href="{{ route('admin.templates.index') }}" class="btn btn-outline-secondary rounded-3">
      <i class="fa-solid fa-arrow-left me-1"></i> Kembali
    </a>
  </div>

  {{-- Alert Error --}}
  @if ($errors->any())
    <div class="alert alert-danger">
      <div class="fw-bold mb-1">Terjadi kesalahan:</div>
      <ul class="mb-0">
        @foreach ($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- Alert Success --}}
  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  {{-- Alert Error Session --}}
  @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <div class="card shadow-sm rounded-4">
    <div class="card-body">

      <form method="POST" action="{{ route('admin.templates.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="row g-3">

          {{-- Nama Template --}}
          <div class="col-md-6">
            <label class="form-label fw-semibold">Nama Template</label>
            <input type="text"
                   name="name"
                   class="form-control"
                   placeholder="Contoh: Template BPMP Kaltim"
                   value="{{ old('name') }}"
                   required>
          </div>

          {{-- Kode Template --}}
          <div class="col-md-6">
            <label class="form-label fw-semibold">Kode Template</label>
            <input type="text"
                   name="code"
                   class="form-control"
                   placeholder="Contoh: BPMPKALTIM"
                   value="{{ old('code') }}"
                   required>
            <div class="text-muted small mt-1">
              Kode harus unik, biasanya huruf besar tanpa spasi.
            </div>
          </div>

          {{-- Upload File --}}
          <div class="col-md-12">
            <label class="form-label fw-semibold">File Template</label>
            <input type="file"
                   name="file"
                   class="form-control"
                   accept=".png,.jpg,.jpeg,.pdf"
                   required>

            <div class="text-muted small mt-1">
              Format yang disarankan: PNG/JPG (background sertifikat) atau PDF.
            </div>
          </div>

          {{-- Aktif / Tidak --}}
          <div class="col-md-6">
            <label class="form-label fw-semibold">Status Template</label>
            <select name="is_active" class="form-select">
              <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Aktif</option>
              <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Nonaktif</option>
            </select>
          </div>

          {{-- Settings JSON (opsional) --}}
          <div class="col-md-6">
            <label class="form-label fw-semibold">Pengaturan (Opsional)</label>
            <textarea name="settings"
                      class="form-control"
                      rows="3"
                      placeholder='{"font":"Arial","size":14}'>{{ old('settings') }}</textarea>
            <div class="text-muted small mt-1">
              Boleh dikosongkan. Format JSON jika diperlukan.
            </div>
          </div>

        </div>

        <hr class="my-4">

        <div class="d-flex justify-content-end gap-2">
          <a href="{{ route('admin.templates.index') }}" class="btn btn-outline-secondary rounded-3">
            Batal
          </a>

          <button type="submit" class="btn btn-primary rounded-3">
            <i class="fa-solid fa-save me-1"></i> Simpan Template
          </button>
        </div>

      </form>

    </div>
  </div>
</div>
@endsection
