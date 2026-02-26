@extends('layouts.app')
@section('title','Kelola Peserta')

@section('content')
@php
  /** @var \Illuminate\Pagination\LengthAwarePaginator $participants */
  $events  = $events ?? collect();

  $q       = $q ?? request('q', '');
  $eventId = $eventId ?? request('event_id', '');
  $status  = $status ?? request('status', '');
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
  <div>
    <h4 class="mb-0">Kelola Peserta</h4>
    <div class="text-muted">Kelola peserta berdasarkan event.</div>
  </div>
  

  <div class="d-flex flex-wrap gap-2">
    <a href="{{ route('admin.participants.import.form', ['event_id' => $eventId]) }}"
       class="btn btn-outline-primary rounded-3">
      <i class="fa-solid fa-file-import me-1"></i> Import
    </a>

    <a href="{{ route('admin.participants.create', ['event_id' => $eventId]) }}"
       class="btn btn-primary rounded-3">
      <i class="fa-solid fa-plus me-1"></i> Tambah Peserta
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

{{-- FILTER CARD --}}
<form method="GET" action="{{ route('admin.participants.index') }}" class="card border-0 shadow-sm rounded-4 mb-3">
  <div class="card-body">
    <div class="row g-2 align-items-end">

      <div class="col-lg-5">
        <label class="form-label small text-muted mb-1">Cari</label>
        <input type="text"
               name="q"
               class="form-control"
               value="{{ $q }}"
               placeholder="Cari nama / email / NIK / instansi...">
      </div>

      <div class="col-lg-4">
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

      <div class="col-lg-2">
        <label class="form-label small text-muted mb-1">Status</label>
        <select name="status" class="form-select">
          <option value="">-- Semua --</option>
          <option value="draft"  @selected($status === 'draft')>Draft</option>
          <option value="terbit" @selected($status === 'terbit')>Terbit</option>
        </select>
      </div>

      <div class="col-lg-1 d-flex gap-2">
        <button class="btn btn-primary w-100" title="Cari">
          <i class="fa-solid fa-magnifying-glass"></i>
        </button>
        <a class="btn btn-outline-secondary" href="{{ route('admin.participants.index') }}" title="Reset">
          <i class="fa-solid fa-rotate-left"></i>
        </a>
      </div>

    </div>
  </div>
</form>

{{-- TABLE CARD --}}
<div class="card border-0 shadow-sm rounded-4">
  <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
    <div class="fw-semibold">Daftar Peserta</div>
    <div class="text-muted small">
      Total: {{ $participants->total() }}
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th width="5%">#</th>
          <th>Nama</th>
          <th>Email</th>
          <th>Event</th>
          <th>Status</th>
          <th width="12%">Aksi</th>
        </tr>
      </thead>

      <tbody>
        @forelse($participants as $p)
          <tr>
            <td>{{ ($participants->currentPage()-1)*$participants->perPage() + $loop->iteration }}</td>

            <td class="fw-semibold">
              {{ $p->name }}
              @if($p->institution)
                <div class="text-muted small">{{ $p->institution }}</div>
              @endif
            </td>

            <td>{{ $p->email ?? '-' }}</td>

            <td class="text-truncate" style="max-width: 320px;">
              {{ $p->event?->name ?? '-' }}
            </td>

            <td>
              @php
              $label = $p->cert_status ?? $p->status ?? 'draft';

              $badgeMap = [
                'draft' => 'bg-secondary',
                'pending' => 'bg-warning',
                'submitted' => 'bg-warning',
                'approved' => 'bg-primary',
                'rejected' => 'bg-danger',
                'final_generated' => 'bg-info',
                'signed' => 'bg-success',
              ];

              $badge = $badgeMap[$label] ?? 'bg-secondary';
            @endphp

              <span class="badge {{ $badge }}">{{ strtoupper($label) }}</span>
            </td>

            <td class="d-flex gap-2">
              <a href="{{ route('admin.participants.edit', $p->id) }}"
                 class="btn btn-warning btn-sm rounded-3"
                 title="Edit">
                <i class="fa-solid fa-pen-to-square"></i>
              </a>

              <form action="{{ route('admin.participants.destroy', $p->id) }}"
                    method="POST"
                    onsubmit="return confirm('Yakin hapus peserta ini?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger btn-sm rounded-3" title="Hapus">
                  <i class="fa-solid fa-trash"></i>
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="text-center text-muted py-4">Belum ada peserta.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

{{-- PAGINATION FOOTER (RAPI SEPERTI DATATABLES) --}}
@if($participants && $participants->hasPages())
  <div class="card-footer bg-white border-0">
    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">

      <div class="text-muted small">
        Showing <strong>{{ $participants->firstItem() }}</strong>
        to <strong>{{ $participants->lastItem() }}</strong>
        of <strong>{{ $participants->total() }}</strong> entries
      </div>

      <div class="d-flex justify-content-end">
        {{ $participants->onEachSide(1)->links() }}
      </div>

    </div>
  </div>
@endif

</div>
@endsection


=