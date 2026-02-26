@extends('layouts.app')
@section('title','Import Peserta')

@section('content')
@php
  $events  = $events ?? collect();
  $eventId = $eventId ?? request('event_id');
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
  <div>
    <h4 class="mb-0">Import Peserta</h4>
    <div class="text-muted">Upload CSV untuk menambahkan peserta per event.</div>
  </div>

  <div class="d-flex gap-2">
    <a href="{{ route('admin.participants.template') }}" class="btn btn-outline-primary rounded-3">
    <i class="fa-solid fa-download me-1"></i> Template CSV
    </a>

    <a href="{{ route('admin.participants.index', ['event_id' => $eventId]) }}" class="btn btn-outline-secondary rounded-3">
      <i class="fa-solid fa-arrow-left me-1"></i> Kembali
    </a>
  </div>
</div>

@if(session('success'))
  <div class="alert alert-success alert-dismissible fade show">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

@if(session('error'))
  <div class="alert alert-danger alert-dismissible fade show">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

<div class="card border-0 shadow-sm rounded-4">
  <div class="card-body">
    <div class="alert alert-info">
      <div class="fw-semibold mb-1">Format CSV</div>
      <div class="small">
        Header yang tersedia: <code>name</code> (wajib),
        <code>email</code>, <code>nik</code>, <code>institution</code>, <code>status</code> (draft/terbit).<br>
        Gunakan tombol <b>Template CSV</b> untuk contoh format.
      </div>
    </div>

    <form method="POST" action="{{ route('admin.participants.import.store') }}" enctype="multipart/form-data">
      @csrf

      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Pilih Event <span class="text-danger">*</span></label>
          <select name="event_id" class="form-select @error('event_id') is-invalid @enderror" required>
            <option value="">-- Pilih Event --</option>
            @foreach($events as $ev)
              <option value="{{ $ev->id }}" @selected((string)old('event_id', $eventId) === (string)$ev->id)>
                {{ $ev->name }}
              </option>
            @endforeach
          </select>
          @error('event_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
          <label class="form-label">File CSV <span class="text-danger">*</span></label>
          <input type="file"
                 name="file"
                 accept=".csv,.txt"
                 class="form-control @error('file') is-invalid @enderror"
                 required>
          @error('file') <div class="invalid-feedback">{{ $message }}</div> @enderror
          <div class="form-text">Maks 5MB. Disarankan CSV UTF-8 (template sudah UTF-8 BOM untuk Excel).</div>
        </div>
      </div>

      <hr class="my-4">

      <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('admin.participants.index', ['event_id' => $eventId]) }}" class="btn btn-outline-secondary rounded-3">
          Batal
        </a>
        <button class="btn btn-primary rounded-3">
          <i class="fa-solid fa-file-arrow-up me-1"></i> Proses Import
        </button>
      </div>
    </form>
  </div>
</div>
@endsection
