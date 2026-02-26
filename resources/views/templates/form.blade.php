@php
  $isEdit = ($mode ?? 'create') === 'edit';
  $t = $template ?? null;

  // normalisasi settings untuk ditampilkan
  $settingsValue = old('settings');

  if ($settingsValue === null) {
    $raw = $t->settings ?? '';
    if (is_array($raw)) {
      $settingsValue = json_encode($raw, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    } elseif (is_string($raw) && trim($raw) !== '') {
      // kalau tersimpan string JSON / string biasa
      $decoded = json_decode($raw, true);
      $settingsValue = json_last_error() === JSON_ERROR_NONE
        ? json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        : $raw;
    } else {
      $settingsValue = '';
    }
  }
@endphp

<div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-3">
  <div>
    <h4 class="mb-0">{{ $isEdit ? 'Edit Template' : 'Tambah Template' }}</h4>
    <div class="text-muted">Atur background dan settings (JSON) untuk template.</div>
  </div>

  <a href="{{ route('admin.system.templates.index') }}" class="btn btn-outline-secondary rounded-3">
    <i class="fa-solid fa-arrow-left me-1"></i> Kembali
  </a>
</div>

@if(session('success'))
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

@if(session('error'))
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

@if($errors->any())
  <div class="alert alert-danger">
    <div class="fw-semibold mb-1">Periksa input:</div>
    <ul class="mb-0">
      @foreach($errors->all() as $e)
        <li>{{ $e }}</li>
      @endforeach
    </ul>
  </div>
@endif

<form method="POST"
      action="{{ $isEdit ? route('admin.system.templates.update', $t) : route('admin.system.templates.store') }}"
      enctype="multipart/form-data"
      class="card border-0 shadow-sm rounded-4">

  @csrf
  @if($isEdit) @method('PATCH') @endif

  <div class="card-body">
    <div class="row g-3">

      <div class="col-md-6">
        <label class="form-label">Nama Template</label>
        <input type="text"
               name="name"
               value="{{ old('name', $t->name ?? '') }}"
               class="form-control @error('name') is-invalid @enderror"
               placeholder="Contoh: Template Penghargaan"
               required
               maxlength="120"
               autocomplete="off">
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="col-md-6">
        <label class="form-label">Kode</label>
        <input type="text"
               name="code"
               value="{{ old('code', $t->code ?? '') }}"
               class="form-control @error('code') is-invalid @enderror"
               placeholder="Contoh: P01"
               required
               maxlength="30"
               autocomplete="off"
               style="text-transform:uppercase"
               oninput="this.value=this.value.toUpperCase()">
        <div class="form-text">Kode unik untuk identifikasi template.</div>
      {{-- DESKRIPSI (opsional) --}}
      <div class="mb-3">
        <label class="form-label">Deskripsi Template (opsional)</label>
        <textarea name="description"
                  rows="3"
                  class="form-control @error('description') is-invalid @enderror"
                  placeholder="Contoh: Template untuk Piagam Penghargaan...">{{ old('description', $template->description ?? '') }}</textarea>
        @error('description')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Catatan internal template (tidak wajib tampil di sertifikat).</div>
      </div>


        @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="col-md-6">
        <label class="form-label">File Background (PNG/JPG/PDF) (opsional)</label>
        <input type="file"
               name="file"
               class="form-control @error('file') is-invalid @enderror"
               accept=".png,.jpg,.jpeg,.pdf,image/png,image/jpeg,application/pdf">
        @error('file') <div class="invalid-feedback">{{ $message }}</div> @enderror

        @if($isEdit && !empty($t?->file_path))
          <div class="form-text">
            File saat ini:
            {{-- Lebih aman pakai route download/preview internal kalau kamu punya --}}
            <a href="{{ asset('storage/'.$t->file_path) }}" target="_blank" rel="noopener">lihat</a>
          </div>
        @endif

        <div class="form-text">
          Saran: PNG untuk background. Jika PDF, pastikan 1 halaman.
        </div>
      </div>

      <div class="col-md-6">
        <label class="form-label">Status</label>
        <select name="is_active" class="form-select @error('is_active') is-invalid @enderror">
          <option value="1" {{ (int)old('is_active', (int)($t->is_active ?? 1)) === 1 ? 'selected' : '' }}>Aktif</option>
          <option value="0" {{ (int)old('is_active', (int)($t->is_active ?? 1)) === 0 ? 'selected' : '' }}>Nonaktif</option>
        </select>
        <div class="form-text">Hanya template aktif yang muncul saat memilih template di Event.</div>
        @error('is_active') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="col-12">
        <label class="form-label">Settings (JSON) (opsional)</label>
        <textarea name="settings"
                  rows="10"
                  class="form-control font-monospace @error('settings') is-invalid @enderror"
                  placeholder='Contoh:
{
  "page": "A4",
  "name_x": 100,
  "name_y": 200,
  "qr_x": 900,
  "qr_y": 600
}'>{{ $settingsValue }}</textarea>
        @error('settings') <div class="invalid-feedback">{{ $message }}</div> @enderror

        <div class="form-text">
          Jika diisi, harus JSON valid. Nanti dipakai untuk posisi nama, nomor, QR, dll.
        </div>
      </div>

    </div>
  </div>

  <div class="card-footer bg-white d-flex justify-content-end gap-2">
    <a href="{{ route('admin.system.templates.index') }}" class="btn btn-outline-secondary rounded-3">Batal</a>
    <button class="btn btn-primary rounded-3" type="submit">
      <i class="fa-solid fa-floppy-disk me-1"></i> Simpan
    </button>
  </div>
</form>
