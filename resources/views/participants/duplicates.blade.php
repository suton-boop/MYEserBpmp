@extends('layouts.app')
@section('title', 'Deteksi Duplikat Peserta')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
  <div>
    <h4 class="fw-bold mb-1">Deteksi Duplikat Data</h4>
    <div class="text-muted small">Mendeteksi peserta dengan NIK, Email, atau Nama yang sama.</div>
  </div>

  <div class="d-flex gap-2">
    @if($eventId)
      <a href="{{ route('admin.participants.index', ['event_id' => $eventId]) }}" class="btn btn-light rounded-3 btn-sm px-3 py-2 border shadow-sm">
        <i class="fa-solid fa-arrow-left me-1 small"></i> Kembali
      </a>
    @else
      <a href="{{ route('admin.participants.index') }}" class="btn btn-light rounded-3 btn-sm px-3 py-2 border shadow-sm">
        <i class="fa-solid fa-arrow-left me-1 small"></i> Kembali
      </a>
    @endif
  </div>
</div>

{{-- FILTER EVENT --}}
<div class="card border-0 shadow-sm rounded-4 mb-3">
  <div class="card-body">
    <form action="{{ route('admin.participants.duplicates') }}" method="GET" class="row g-2 align-items-end">
      <input type="hidden" name="type" value="{{ $type }}">
      <div class="col-md-6">
        <label class="form-label small fw-bold">Filter Berdasarkan Event</label>
        <select name="event_id" class="form-select rounded-3" onchange="this.form.submit()">
          <option value="">-- Semua Event (Cek duplikasi di tiap event) --</option>
          @foreach($events as $ev)
            <option value="{{ $ev->id }}" {{ (string)$eventId === (string)$ev->id ? 'selected' : '' }}>{{ $ev->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-auto">
        <a href="{{ route('admin.participants.duplicates', ['type' => $type]) }}" class="btn btn-light rounded-3 border">Reset</a>
      </div>
    </form>
  </div>
</div>

{{-- TAB BUTTONS --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
  <div class="card-body p-2">
    <div class="nav nav-pills nav-justified gap-2">
      <a href="{{ route('admin.participants.duplicates', ['type' => 'nik', 'event_id' => $eventId]) }}" 
         class="nav-link rounded-3 {{ $type === 'nik' ? 'active' : 'bg-light text-dark' }}">
        <i class="fa-solid fa-id-card me-1"></i> Berdasarkan NIK
      </a>
      <a href="{{ route('admin.participants.duplicates', ['type' => 'email', 'event_id' => $eventId]) }}" 
         class="nav-link rounded-3 {{ $type === 'email' ? 'active' : 'bg-light text-dark' }}">
        <i class="fa-solid fa-envelope me-1"></i> Berdasarkan Email
      </a>
      <a href="{{ route('admin.participants.duplicates', ['type' => 'name', 'event_id' => $eventId]) }}" 
         class="nav-link rounded-3 {{ $type === 'name' ? 'active' : 'bg-light text-dark' }}">
        <i class="fa-solid fa-user me-1"></i> Berdasarkan Nama
      </a>
    </div>
  </div>
</div>

@if($participants->isEmpty())
  <div class="card border-0 shadow-sm rounded-4">
    <div class="card-body text-center py-5">
      <div class="mb-3">
        <i class="fa-solid fa-circle-check text-success fa-3x"></i>
      </div>
      <h5 class="fw-bold">Tidak ditemukan data duplikat!</h5>
      <p class="text-muted">
        Seluruh data peserta berdasarkan <strong>{{ strtoupper($type) }}</strong> 
        {{ $eventId ? 'pada event ini' : 'di masing-masing event' }} saat ini unik.
      </p>
    </div>
  </div>
@else
  <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
      <table class="table mb-0">
        <thead>
          <tr>
            <th width="50" class="text-center">#</th>
            <th>Nama / Instansi</th>
            <th>{{ strtoupper($type) }}</th>
            <th>Event</th>
            <th>Status</th>
            <th width="100">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @php
            $currentValue = null;
            $rowColor = '#ffffff';
          @endphp
          @foreach($participants as $index => $p)
            @php
              // Beri tanda atau warna beda jika group berubah
              if ($currentValue !== $p->{$type}) {
                  $currentValue = $p->{$type};
                  $rowColor = $rowColor === '#ffffff' ? '#f8fafc' : '#ffffff';
              }
            @endphp
            <tr style="background-color: {{ $rowColor }}">
              <td class="text-center">{{ $index + 1 }}</td>
              <td>
                <div class="fw-bold">{{ $p->name }}</div>
                <div class="text-muted small">{{ $p->institution ?? '-' }}</div>
              </td>
              <td>
                <span class="badge {{ $type === 'nik' ? 'text-bg-primary' : ($type === 'email' ? 'text-bg-info' : 'text-bg-secondary') }} rounded-pill px-3">
                    {{ $p->{$type} }}
                </span>
                @if($currentValue === $p->{$type})
                   <i class="fa-solid fa-triangle-exclamation text-warning ms-1" title="Duplikat"></i>
                @endif
              </td>
              <td>
                <div class="small fw-semibold">{{ $p->event->name ?? '-' }}</div>
              </td>
              <td class="text-center">
                 @if($p->status === 'terbit')
                    <span class="badge text-bg-success">Terbit</span>
                 @else
                    <span class="badge text-bg-secondary">Draft</span>
                 @endif
              </td>
              <td>
                 <div class="d-flex gap-1">
                    <a href="{{ route('admin.participants.edit', $p->id) }}" class="btn btn-sm btn-outline-primary rounded-3">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                    <form action="{{ route('admin.participants.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Hapus data ini?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger rounded-3">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                 </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  
  <div class="alert alert-info border-0 shadow-sm rounded-4 mt-4">
    <i class="fa-solid fa-circle-info me-2"></i>
    <strong>Info:</strong> Data di atas hanya menampilkan peserta yang memiliki {{ strtoupper($type) }} ganda <strong>dalam satu event yang sama</strong>. Peserta yang terdaftar di event berbeda dengan {{ strtoupper($type) }} yang sama tidak akan dianggap duplikat di sini.
  </div>
@endif

@endsection
