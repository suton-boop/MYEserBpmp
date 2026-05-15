@extends('layouts.app')
@section('title','Daftar Penolakan Sertifikat')

@section('content')
@php
  $events = $events ?? collect();
  $eventId = $eventId ?? request('event_id', '');
@endphp

<div class="mb-3">
  <h4 class="mb-0">Daftar Penolakan Sertifikat</h4>
  <div class="text-muted">Daftar sertifikat yang ditolak (<b>REJECTED</b>) beserta alasan penolakannya.</div>
</div>

<div class="mb-4">
    <a href="{{ route('admin.system.approvals.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
        <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Persetujuan
    </a>
</div>

{{-- FILTER --}}
<form method="GET" action="{{ route('admin.system.approvals.rejected') }}" class="card border-0 shadow-sm rounded-4 mb-3">
  <div class="card-body">
    <div class="row g-2 align-items-end">
      <div class="col-lg-6">
        <label class="form-label small text-muted mb-1">Event</label>
        <select name="event_id" class="form-select">
          <option value="">-- Semua Event --</option>
          @foreach($events as $ev)
            <option value="{{ $ev->id }}" @selected((string)$eventId === (string)$ev->id)>
              {{ $ev->name }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="col-lg-3 d-flex gap-2">
        <button class="btn btn-primary w-100" title="Filter">
          <i class="fa-solid fa-magnifying-glass me-1"></i> Filter
        </button>
        <a class="btn btn-outline-secondary" href="{{ route('admin.system.approvals.rejected') }}" title="Reset">
          Reset
        </a>
      </div>

      <div class="col-lg-3 text-end text-muted small">
        Total: {{ $certificates->total() }}
      </div>
    </div>
  </div>
</form>

<div class="card border-0 shadow-sm rounded-4">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th width="5%">#</th>
          <th>Peserta / Event</th>
          <th>Waktu Penolakan</th>
          <th width="30%">Alasan Penolakan</th>
          <th width="15%" class="text-center">Status</th>
        </tr>
      </thead>
      <tbody>
        @forelse($certificates as $c)
          <tr>
            <td>{{ ($certificates->currentPage()-1)*$certificates->perPage() + $loop->iteration }}</td>
            <td class="fw-semibold">
              {{ $c->participant?->name ?? '-' }}
              <div class="text-muted small">Event: {{ $c->event?->name ?? '-' }}</div>
              <div class="text-muted small" style="font-weight: 400;">Diajukan oleh: {{ $c->submittedBy?->name ?? 'System' }}</div>
            </td>
            <td>
              <div class="fw-bold text-danger small">Ditolak pada:</div>
              <div class="text-muted small">{{ $c->rejected_at?->format('d M Y H:i') ?? '-' }}</div>
            </td>
            <td>
              <div class="bg-light p-2 rounded small border-start border-danger border-4">
                {{ $c->rejected_note ?? 'Tidak ada catatan.' }}
              </div>
            </td>
            <td class="text-center">
                <span class="badge bg-danger">REJECTED</span>
                <div class="mt-2">
                    <a href="{{ route('admin.participants.edit', $c->participant_id) }}" class="btn btn-outline-primary btn-sm rounded-pill" title="Perbaiki data peserta">
                        <i class="fa-solid fa-user-pen me-1"></i> Perbaiki
                    </a>
                </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="text-center text-muted py-4">
              Tidak ada data sertifikat yang ditolak.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($certificates->hasPages())
    <div class="card-footer bg-white border-0">
      {{ $certificates->links() }}
    </div>
  @endif
</div>
@endsection
