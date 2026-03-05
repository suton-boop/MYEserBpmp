@extends('layouts.app')
@section('title','Edit Event')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h4 class="mb-0">Edit Event</h4>
    <div class="text-muted">Ubah data event.</div>
  </div>
  <a href="{{ route('admin.system.events.index') }}" class="btn btn-outline-secondary rounded-3">Kembali</a>
</div>

@if ($errors->any())
  <div class="alert alert-danger">
    <div class="fw-semibold mb-1">Periksa input:</div>
    <ul class="mb-0">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

<form method="POST" action="{{ route('admin.system.events.update', $event->id) }}" class="card border-0 shadow-sm rounded-4">
  @csrf
  @method('PATCH')

  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-8">
        <label class="form-label">Nama Event <span class="text-danger">*</span></label>
        <input name="name" class="form-control" value="{{ old('name', $event->name) }}" required>
      </div>

      <div class="col-md-4">
        <label class="form-label">Status <span class="text-danger">*</span></label>
        @php $st = old('status', $event->status); @endphp
        <select name="status" class="form-select" required>
          <option value="draft" @selected($st==='draft')>draft</option>
          <option value="active" @selected($st==='active')>active</option>
          <option value="closed" @selected($st==='closed')>closed</option>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
        <input type="date" name="start_date" class="form-control"
               value="{{ old('start_date', optional($event->start_date)->format('Y-m-d')) }}" required>
      </div>

      <div class="col-md-4">
        <label class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
        <input type="date" name="end_date" class="form-control"
               value="{{ old('end_date', optional($event->end_date)->format('Y-m-d')) }}" required>
      </div>

      <div class="col-md-4">
        <label class="form-label">Lokasi <span class="text-danger">*</span></label>
        <input name="location" class="form-control" value="{{ old('location', $event->location) }}" required>
      </div>

      <div class="col-12">
        <label class="form-label">Deskripsi <span class="text-danger">*</span></label>
        <textarea name="description" rows="4" class="form-control" required>{{ old('description', $event->description) }}</textarea>
      </div>

      <div class="col-12">
        <label class="form-label">Pilih Template Sertifikat (opsional)</label>
        <select name="certificate_template_id" class="form-select @error('certificate_template_id') is-invalid @enderror">
          <option value="">-- Default / Belum ditentukan --</option>
          @foreach($templates as $t)
            <option value="{{ $t->id }}"
              @selected((string)old('certificate_template_id', $event->certificate_template_id) === (string)$t->id)>
              {{ $t->name }} @if(!$t->is_active) (Nonaktif) @endif
            </option>
          @endforeach
        </select>
        <div class="form-text">Template ini dipakai saat generate sertifikat untuk event ini.</div>
        @error('certificate_template_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>
    </div>
  </div>
 <div class="card-footer bg-white d-flex justify-content-end gap-2">
    <button class="btn btn-primary rounded-3">
      <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan
    </button>
  </div>
</form>
@endsection
