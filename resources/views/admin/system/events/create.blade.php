@extends('layouts.app')
@section('title','Tambah Event')


@section('content')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
  <div>
    <h4 class="mb-0">Tambah Event</h4>
    <div class="text-muted">Buat event baru dan pilih template sertifikat (opsional).</div>
  </div>

  <a href="{{ route('admin.system.events.index') }}" class="btn btn-outline-secondary">
    <i class="fa-solid fa-arrow-left me-1"></i> Kembali
  </a>
</div>

@if ($errors->any())
  <div class="alert alert-danger">
    <div class="fw-semibold mb-1">Periksa kembali input:</div>
    <ul class="mb-0">
      @foreach ($errors->all() as $err)
        <li>{{ $err }}</li>
      @endforeach
    </ul>
  </div>
@endif

<form method="POST" action="{{ route('admin.system.events.store') }}" class="card border-0 shadow-sm rounded-4">
  @csrf
  <div class="card-body p-4">
    <div class="row g-3">

      <div class="col-md-8">
        <label class="form-label fw-semibold">Nama Event <span class="text-danger">*</span></label>
        <input type="text"
               name="name"
               class="form-control"
               value="{{ old('name') }}"
               placeholder="Contoh: Penguatan Literasi"
               required>
      </div>

      <div class="col-md-4">
        <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
        <select name="status" class="form-select" required>
          <option value="active" @selected(old('status', 'active') === 'active')>Aktif (Active)</option>
          <option value="draft" @selected(old('status') === 'draft')>Draf (Draft)</option>
          <option value="closed" @selected(old('status') === 'closed')>Selesai (Closed)</option>
        </select>
        <div class="form-text">Event nonaktif tidak muncul di proses operasional.</div>
      </div>

      <div class="col-md-6">
        <label class="form-label fw-semibold">Tanggal Mulai <span class="text-danger">*</span></label>
        <input type="date"
               name="start_date"
               class="form-control"
               value="{{ old('start_date') }}">
      </div>

      <div class="col-md-6">
        <label class="form-label fw-semibold">Tanggal Selesai <span class="text-danger">*</span></label>
        <input type="date"
               name="end_date"
               class="form-control"
               value="{{ old('end_date') }}" required>
      </div>

      <div class="col-12">
        <label class="form-label fw-semibold">Lokasi <span class="text-danger">*</span></label>
        <input type="text"
               name="location"
               class="form-control"
               value="{{ old('location') }}"
               placeholder="Contoh: Perpustakaan BPMP Kaltim" required>
      </div>

      <div class="col-12">
        <label class="form-label fw-semibold">Deskripsi <span class="text-danger">*</span></label>
        <textarea name="description" class="form-control" rows="4"
                  placeholder="Catatan event, keterangan kegiatan, dll." required>{{ old('description') }}</textarea>
      </div>

      <div class="col-12">
        <label class="form-label fw-semibold">Template Sertifikat (opsional)</label>
        <select name="certificate_template_id" class="form-select">
          <option value="">-- Pilih Template --</option>

          {{-- Controller boleh kirim $templates, tapi kalau belum ada, tidak error --}}
          @if(!empty($templates))
            @foreach($templates as $t)
              <option value="{{ $t->id }}" @selected((string)old('certificate_template_id') === (string)$t->id)>
                {{ $t->name }} ({{ $t->code }})
              </option>
            @endforeach
          @endif
        </select>
        <div class="form-text">Hanya template aktif yang sebaiknya dipilih.</div>
      </div>

    </div>
  </div>

  <div class="card-footer bg-white border-0 p-4 pt-0 d-flex justify-content-end gap-2">
    <a href="{{ route('admin.system.events.index') }}" class="btn btn-outline-secondary">Batal</a>
    <button class="btn btn-primary">
      <i class="fa-solid fa-floppy-disk me-1"></i> Simpan
    </button>
  </div>
</form>
@endsection