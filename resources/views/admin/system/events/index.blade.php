@extends('layouts.app')
@section('title','Manajemen Event')

@section('content')
@php
  $q      = $q ?? request('q', '');
  $status = $status ?? request('status', '');
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
  <div>
    <h4 class="mb-0">Manajemen Event</h4>
    <div class="text-muted">Kelola daftar event untuk e-sertifikat.</div>
  </div>

  @if(!in_array(strtolower(auth()->user()->role?->name ?? ''), ['operator']))
  <a href="{{ route('admin.system.events.create') }}" class="btn btn-primary rounded-3">
    <i class="fa-solid fa-plus me-1"></i> Tambah Event
  </a>
  @endif
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

{{-- FILTER CARD --}}
<form method="GET" action="{{ route('admin.system.events.index') }}" class="card border-0 shadow-sm rounded-4 mb-3">
  <div class="card-body">
    <div class="row g-2 align-items-end">
      <div class="col-lg-6">
        <label class="form-label small text-muted mb-1">Cari</label>
        <input
          type="text"
          name="q"
          class="form-control"
          value="{{ $q }}"
          placeholder="Cari nama event / lokasi..."
        >
      </div>

      <div class="col-lg-3">
        <label class="form-label small text-muted mb-1">Status</label>
        <select name="status" class="form-select">
          <option value="">-- Semua Status --</option>
          <option value="proposed" @selected($status==='proposed')>Proposed</option>
          <option value="draft"  @selected($status==='draft')>Draft</option>
          <option value="active" @selected($status==='active')>Active</option>
          <option value="closed" @selected($status==='closed')>Closed</option>
        </select>
      </div>

      <div class="col-lg-3 d-flex gap-2">
        <button class="btn btn-primary w-100" title="Cari" type="submit">
          <i class="fa-solid fa-magnifying-glass me-1"></i> Cari
        </button>
        <a class="btn btn-outline-secondary" href="{{ route('admin.system.events.index') }}" title="Reset">
          Reset
        </a>
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
          <th>Nama Event</th>
          <th>Tanggal</th>
          <th>Lokasi</th>
          <th width="10%" class="text-center">Peserta</th>
          <th width="10%">Status</th>
          @if(!in_array(strtolower(auth()->user()->role?->name ?? ''), ['operator']))
          <th width="12%">Aksi</th>
          @endif
        </tr>
      </thead>

      <tbody>
        @forelse($events as $e)
          @php
            $st = $e->status ?? 'draft';

            $badge = match($st) {
              'active'   => 'bg-success',
              'closed'   => 'bg-secondary',
              'proposed' => 'bg-info text-dark',
              'draft'    => 'bg-warning text-dark',
              default    => 'bg-light text-dark',
            };

            $label = match($st) {
              'active'   => 'Active',
              'closed'   => 'Closed',
              'proposed' => 'Proposed',
              'draft'    => 'Draft',
              default    => ucfirst((string)$st),
            };

            $start = $e->start_date?->format('d M Y');
            $end   = $e->end_date?->format('d M Y');
          @endphp

          <tr>
            <td>{{ ($events->currentPage()-1)*$events->perPage() + $loop->iteration }}</td>

            <td class="fw-semibold">
              {{ $e->name }}
              @if(!empty($e->description))
                <div class="text-muted small text-truncate" style="max-width:520px;">
                  {{ $e->description }}
                </div>
              @endif
            </td>

            <td>
              {{ $start ?? '-' }}
              @if($end)
                <span class="text-muted">-</span> {{ $end }}
              @endif
            </td>

            <td>{{ $e->location ?? '-' }}</td>

            <td class="text-center">
              <span class="badge bg-info">
                {{ $e->participants_count ?? 0 }}
              </span>
            </td>

            <td>
              <span class="badge {{ $badge }}">{{ $label }}</span>
            </td>

            @if(!in_array(strtolower(auth()->user()->role?->name ?? ''), ['operator']))
            <td>
              <div class="d-flex gap-2">
                @if(in_array(strtolower(auth()->user()->role?->name ?? ''), ['admin', 'superadmin', 'super admin', 'admin_sistem', 'pimpinan', 'kasubag', 'kasubbag', 'kepala']))
                  @if($st === 'proposed')
                  <form action="{{ route('admin.system.events.approve', $e->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button class="btn btn-primary btn-sm rounded-3" title="Setujui Event" type="submit" onclick="return confirm('Setujui event ini agar aktif?')">
                      <i class="fa-solid fa-check-double"></i> Setujui
                    </button>
                  </form>
                  @endif

                  <a
                    href="{{ route('admin.system.events.downloadSigned', $e->id) }}"
                    class="btn btn-success btn-sm rounded-3"
                    title="Download Sertifikat TTE"
                  >
                    <i class="fa-solid fa-file-pdf"></i>
                  </a>
                @endif

                <a
                  href="{{ route('admin.system.events.edit', $e->id) }}"
                  class="btn btn-warning btn-sm rounded-3"
                  title="Edit"
                >
                  <i class="fa-solid fa-pen-to-square"></i>
                </a>

                <form
                  action="{{ route('admin.system.events.destroy', $e->id) }}"
                  method="POST"
                  onsubmit="return confirm('Yakin hapus event ini?')"
                >
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-danger btn-sm rounded-3" title="Hapus" type="submit">
                    <i class="fa-solid fa-trash"></i>
                  </button>
                </form>
              </div>
            </td>
            @endif
          </tr>
        @empty
          <tr>
            <td colspan="{{ auth()->user()->role?->name !== 'operator' ? 7 : 6 }}" class="text-center text-muted py-4">Belum ada event.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($events->hasPages())
    <div class="card-footer bg-white border-0">
      {{ $events->links() }}
    </div>
  @endif
</div>
@endsection