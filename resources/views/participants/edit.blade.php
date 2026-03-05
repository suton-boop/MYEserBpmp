@extends('layouts.app')
@section('title','Edit Peserta')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h4 class="mb-0">Edit Peserta</h4>
    <div class="text-muted">Ubah data peserta.</div>
  </div>
  <a href="{{ route('admin.participants.index', ['event_id' => $participant->event_id]) }}"
     class="btn btn-outline-secondary rounded-3">
    Kembali
  </a>
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

<form method="POST" action="{{ route('admin.participants.update', $participant->id) }}" class="card border-0 shadow-sm rounded-4">
  @csrf
  @method('PATCH')

  <div class="card-body">
    <div class="row g-3">

      <div class="col-md-6">
        <label class="form-label">Event <span class="text-danger">*</span></label>
        <select name="event_id" class="form-select" required>
          @foreach($events as $ev)
            <option value="{{ $ev->id }}" @selected(old('event_id', $participant->event_id) == $ev->id)>
              {{ $ev->name }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Status <span class="text-danger">*</span></label>
        <select name="status" class="form-select" required>
          <option value="draft"  @selected(old('status', $participant->status)==='draft')>Draft</option>
          <option value="terbit" @selected(old('status', $participant->status)==='terbit')>Terbit</option>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Nama <span class="text-danger">*</span></label>
        <input name="name" class="form-control" value="{{ old('name', $participant->name) }}" required>
      </div>

      <div class="col-md-6">
        <label class="form-label">Email <span class="text-danger">*</span></label>
        <input name="email" type="email" class="form-control" value="{{ old('email', $participant->email) }}" required>
      </div>

      <div class="col-md-6">
        <label class="form-label">NIK (opsional)</label>
        <input name="nik" class="form-control" value="{{ old('nik', $participant->nik) }}">
      </div>

      <div class="col-md-6">
        <label class="form-label">Instansi <span class="text-danger">*</span></label>
        <input name="institution" class="form-control" value="{{ old('institution', $participant->institution) }}" required>
      </div>

      <div class="col-md-6">
        <label class="form-label">Kab./Kota/Propinsi <span class="text-danger">*</span></label>
        <select name="daerah" class="form-select" required>
          <option value="">-- Pilih Daerah --</option>
          @foreach(['Prov. Kalimantan Timur', 'Kab. Paser', 'Kab. Berau', 'Kab. Kutai Kartanegara', 'Kab. Kutai Barat', 'Kab. Kutai Timur', 'Kab. Penajam Paser Utara', 'Kab. Mahakam Ulu', 'Kota Balikpapan', 'Kota Samarinda', 'Kota Bontang'] as $d)
            <option value="{{ $d }}" @selected(old('daerah', $participant->daerah) === $d)>{{ $d }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Jenjang <span class="text-danger">*</span></label>
        <select name="jenjang" class="form-select" required>
          <option value="">-- Pilih Jenjang --</option>
          @foreach(['PAUD-TK', 'SD', 'SMP', 'SMA', 'SMK', 'PNF', 'Umum'] as $j)
            <option value="{{ $j }}" @selected(old('jenjang', $participant->jenjang) === $j)>{{ $j }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Peran (opsional)</label>
        <input name="peran" class="form-control" value="{{ old('peran', $participant->peran) }}">
      </div>

      <div class="col-md-12">
        <label class="form-label">Keterangan (opsional)</label>
        <textarea name="keterangan" class="form-control" rows="2">{{ old('keterangan', $participant->keterangan) }}</textarea>
      </div>

      <div class="col-md-12">
        <label class="form-label">Data Tambahan / Metadata (Format JSON, opsional)</label>
        <textarea name="metadata" class="form-control font-monospace" rows="3" placeholder='Contoh: {"nilai_praktek": 95, "jam_pelajaran": 40}'>{{ old('metadata', $participant->metadata ? json_encode($participant->metadata, JSON_PRETTY_PRINT) : '') }}</textarea>
        <div class="form-text">Gunakan format JSON yang valid. Biarkan kosong jika tidak ada data tambahan.</div>
      </div>

    </div>
  </div>

  <div class="card-footer bg-white d-flex justify-content-end">
    <button class="btn btn-primary rounded-3">
      <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan
    </button>
  </div>
</form>
@endsection
