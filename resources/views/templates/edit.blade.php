
@extends('layouts.app')
@section('title','Edit Template')

@section('content')
@php
  // sesuaikan variable dari controller
  // biasanya: $template = CertificateTemplate::findOrFail(...)
  $template = $template ?? null;
@endphp

<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
  <div>
    <div class="text-muted small mb-1">
      <i class="fa-regular fa-folder-open me-1"></i>
      Manajemen Sistem / Template Sertifikat / Edit
    </div>
    <h4 class="page-title mb-1">Edit Template</h4>
    <div class="page-subtitle">Atur background dan settings (JSON) untuk template.</div>
  </div>

  <a href="{{ route('admin.system.templates.index') }}" class="btn btn-outline-secondary btn-icon">
    <i class="fa-solid fa-arrow-left"></i> Kembali
  </a>
</div>

@if ($errors->any())
  <div class="alert alert-danger card-soft">
    <div class="fw-semibold mb-1">Periksa kembali input:</div>
    <ul class="mb-0">
      @foreach ($errors->all() as $err)
        <li>{{ $err }}</li>
      @endforeach
    </ul>
  </div>
@endif

<form method="POST"
      action="{{ route('admin.system.templates.update', $template->id) }}"
      enctype="multipart/form-data">
  @csrf
  @method('PATCH')

  <div class="row g-3">
    {{-- LEFT: MAIN INFO --}}
    <div class="col-lg-7">
      <div class="card card-soft">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="fw-semibold">Informasi Template</div>
            <span class="badge text-bg-light border">ID: {{ $template->id }}</span>
          </div>

          <div class="row g-3 mt-1">
            <div class="col-12">
              <label class="form-label">Nama Template</label>
              <input type="text"
                     name="name"
                     class="form-control"
                     value="{{ old('name', $template->name) }}"
                     placeholder="Contoh: Penguatan Literasi"
                     required>
            </div>

            <div class="col-md-4">
              <label class="form-label">Kode</label>
              <input type="text"
                     name="code"
                     class="form-control"
                     value="{{ old('code', $template->code) }}"
                     placeholder="Contoh: P06"
                     required>
              <div class="form-text">Kode unik untuk identifikasi template.</div>
            </div>

            <div class="col-md-8">
              <label class="form-label">Status</label>
              <select name="is_active" class="form-select">
                <option value="1" @selected(old('is_active', $template->is_active) == 1)>Aktif</option>
                <option value="0" @selected(old('is_active', $template->is_active) == 0)>Nonaktif</option>
              </select>
              <div class="form-text">Hanya template aktif yang muncul saat memilih template di Event.</div>
            </div>

            <div class="col-12">
              <label class="form-label">Deskripsi Template (opsional)</label>
              <textarea name="description"
                        class="form-control"
                        rows="3"
                        placeholder="Catatan internal template (tidak wajib tampil di sertifikat).">{{ old('description', $template->description) }}</textarea>
            </div>
          </div>
        </div>
      </div>

      {{-- BACKGROUND FILE --}}
      <div class="card card-soft mt-3">
        <div class="card-body">
          <div class="fw-semibold mb-2">Background</div>

          <label class="form-label">File Background (PNG/JPG/PDF) <span class="text-muted fw-normal">(opsional)</span></label>
          <input type="file" name="background" class="form-control">

          <div class="d-flex flex-wrap gap-2 align-items-center mt-2">
            <div class="form-text m-0">
              Saran: PNG untuk background. Jika PDF, pastikan 1 halaman.
            </div>

            @if(!empty($template->file_path))
              <a class="btn btn-outline-primary btn-sm btn-icon"
                 href="{{ asset('storage/'.$template->file_path) }}"
                 target="_blank" rel="noopener">
                <i class="fa-regular fa-eye"></i> Lihat file saat ini
              </a>
            @endif
          </div>
        </div>
      </div>
    </div>

    {{-- RIGHT: JSON SETTINGS --}}
    <div class="col-lg-5">
      <div class="card card-soft">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="fw-semibold">Settings (JSON)</div>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-outline-secondary btn-sm btn-icon" onclick="formatJson()">
                <i class="fa-solid fa-wand-magic-sparkles"></i> Format JSON
              </button>
            </div>
          </div>

          <div class="form-text mb-2">
            Jika diisi, harus JSON valid. Dipakai untuk posisi nama, nomor, QR, dll.
          </div>

          <textarea id="settingsJson"
                    name="settings"
                    class="form-control mono"
                    rows="18"
                    placeholder='{"fields":{"name":{"x":0,"y":0,"font":18}}}'>{{ old('settings', is_array($template->settings) ? json_encode($template->settings, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) : ($template->settings ?? '')) }}</textarea>

          <div class="mt-2 small text-muted">
            Tips: gunakan <span class="badge text-bg-light border">JSON_PRETTY</span> agar mudah dibaca.
          </div>
        </div>
      </div>

      <div class="card card-soft mt-3">
        <div class="card-body">
          <div class="fw-semibold mb-1">Catatan</div>
          <div class="text-muted small">
            Setelah simpan, pastikan template <b>Aktif</b> bila ingin muncul saat membuat Event.
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- ACTIONS --}}
  <div class="d-flex justify-content-end gap-2 mt-3">
    <a href="{{ route('admin.system.templates.index') }}" class="btn btn-outline-secondary">Batal</a>
    <button class="btn btn-primary btn-icon">
      <i class="fa-solid fa-floppy-disk"></i> Simpan
    </button>
  </div>
</form>

<script>
function formatJson() {
  const el = document.getElementById('settingsJson');
  if (!el) return;

  const raw = el.value.trim();
  if (!raw) return;

  try {
    const obj = JSON.parse(raw);
    el.value = JSON.stringify(obj, null, 2);
  } catch (e) {
    alert('JSON tidak valid. Periksa tanda koma, kurung, atau kutip.');
  }
}
</script>
@endsection