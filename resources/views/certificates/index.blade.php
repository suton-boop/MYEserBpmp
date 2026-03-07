@extends('layouts.app')
@section('title','Generate Sertifikat')

@section('content')
@php
  $events       = $events ?? collect();
  $participants = $participants ?? null;

  $q       = $q ?? request('q', '');
  $eventId = $eventId ?? request('event_id', '');
  $status  = $status ?? request('status', '');
  $certMap = $certMap ?? collect();
  $sortBy  = $sortBy ?? request('sort', 'latest');
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
  <div>
    <h4 class="fw-bold mb-1">Generate Sertifikat</h4>
    <div class="text-muted small">Kelola penerbitan sertifikat digital secara masal.</div>
  </div>

  <div class="d-flex flex-wrap gap-2">
    {{-- Action Group --}}
    <div class="bg-white p-1 rounded-4 shadow-sm border d-flex gap-1">
        <form method="POST" action="{{ route('admin.certificates.generateAll') }}">
          @csrf
          <input type="hidden" name="event_id" value="{{ $eventId }}">
          <button class="btn btn-primary rounded-3 btn-sm px-3 py-2" {{ $eventId ? '' : 'disabled' }}>
            <i class="fa-solid fa-wand-magic-sparkles me-1 small"></i> Draft Semua
          </button>
        </form>

        <form method="POST" action="{{ route('admin.certificates.submitAll') }}">
          @csrf
          <input type="hidden" name="event_id" value="{{ $eventId }}">
          <button class="btn btn-outline-primary rounded-3 btn-sm px-3 py-2" {{ $eventId ? '' : 'disabled' }}>
            <i class="fa-solid fa-paper-plane me-1 small"></i> Ajukan Semua
          </button>
        </form>

        <form method="POST" action="{{ route('admin.certificates.generatePdfAll') }}">
          @csrf
          <input type="hidden" name="event_id" value="{{ $eventId }}">
          <button class="btn btn-soft-success rounded-3 btn-sm px-3 py-2" {{ $eventId ? '' : 'disabled' }}>
            <i class="fa-solid fa-file-pdf me-1 small"></i> Generate PDF (Approved)
          </button>
        </form>
    </div>
  </div>
</div>

<style>
    .btn-soft-success {
        background-color: rgba(25, 135, 84, 0.1);
        color: #198754;
        border: 1px solid rgba(25, 135, 84, 0.2);
    }
    .btn-soft-success:hover {
        background-color: #198754;
        color: #fff;
    }
    .form-label-top { font-size: 0.75rem; font-weight: 600; color: #6c757d; display: block; margin-bottom: 4px; }
    .filter-card { border: none !important; transition: all 0.3s ease; }
    .filter-card:hover { transform: translateY(-2px); }
</style>

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

<form method="GET" action="{{ route('admin.certificates.index') }}"
      class="card filter-card shadow-sm rounded-4 mb-3 border-0">
  <div class="card-body p-3">
    <div class="row g-3">
      <div class="col-lg-3">
        <label class="form-label-top"><i class="fa-solid fa-magnifying-glass me-1"></i> Cari</label>
        <input type="text" name="q" class="form-control rounded-3" value="{{ $q }}"
               placeholder="Nama, email, atau NIK...">
      </div>

      <div class="col-lg-3">
        <label class="form-label-top"><i class="fa-solid fa-calendar-event me-1"></i> Event</label>
        <select name="event_id" class="form-select rounded-3">
          <option value="">-- Pilih Event --</option>
          @foreach($events as $ev)
            <option value="{{ $ev->id }}" @selected((string)$eventId === (string)$ev->id)>
              {{ $ev->name }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="col-lg-2">
        <label class="form-label-top"><i class="fa-solid fa-tag me-1"></i> Status</label>
        <select name="status" class="form-select rounded-3">
          <option value="">-- Semua --</option>
          <option value="draft" @selected($status === 'draft')>Draft</option>
          <option value="submitted" @selected($status === 'submitted')>Submitted</option>
          <option value="approved" @selected($status === 'approved')>Approved</option>
          <option value="final_generated" @selected($status === 'final_generated')>Final Generated</option>
          <option value="signed" @selected($status === 'signed')>Signed</option>
          <option value="rejected" @selected($status === 'rejected')>Rejected</option>
        </select>
      </div>

      <div class="col-lg-2">
        <label class="form-label-top"><i class="fa-solid fa-sort me-1"></i> Urutan</label>
        <select name="sort" class="form-select rounded-3">
          <option value="latest" @selected($sortBy === 'latest')>Terbaru</option>
          <option value="name_asc" @selected($sortBy === 'name_asc')>Nama A-Z</option>
          <option value="name_desc" @selected($sortBy === 'name_desc')>Nama Z-A</option>
          <option value="oldest" @selected($sortBy === 'oldest')>Terlama</option>
        </select>
      </div>

      <div class="col-lg-2 d-flex align-items-end gap-2">
        <button class="btn btn-primary h-100 flex-grow-1 rounded-3">
          <i class="fa-solid fa-filter me-1"></i> Filter
        </button>
        <a class="btn btn-outline-secondary h-100 px-3 rounded-3 d-flex align-items-center" href="{{ route('admin.certificates.index') }}">
          <i class="fa-solid fa-rotate-left"></i>
        </a>
      </div>
    </div>
  </div>
</form>

<div class="card border-0 shadow-sm rounded-4">
  <div class="card-header bg-white border-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
      <div class="fw-semibold">Daftar Peserta</div>
      <div class="text-muted small">
        File PDF tersimpan di: <code>storage/app/public/{pdf_path}</code> dan bisa diakses:
        <code>public/storage/{pdf_path}</code> (setelah <code>php artisan storage:link</code>)
      </div>
    </div>
    <div class="text-muted small">Total: {{ $participants?->total() ?? 0 }}</div>
  </div>

  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th width="5%">#</th>
          <th>Nama</th>
          <th>Email</th>
          <th>Event</th>
          <th width="16%">Sertifikat</th>
          <th width="22%" class="text-end">Aksi</th>
        </tr>
      </thead>

      <tbody>
      @if(!$participants)
        <tr>
          <td colspan="6" class="text-center text-muted py-4">
            Data peserta belum dikirim dari controller.
          </td>
        </tr>
      @else
        @forelse($participants as $p)
          @php
            $key      = ($p->event_id ?? 0).':'.$p->id;
            $cert     = $certMap->get($key);
            $hasCert  = !empty($cert);
            $hasPdf   = $hasCert && !empty($cert->pdf_path);

            $statusVal = $hasCert ? ($cert->status ?? \App\Models\Certificate::STATUS_DRAFT) : null;

            $badgeClass = match($statusVal) {
              \App\Models\Certificate::STATUS_DRAFT => 'bg-warning text-dark',
              \App\Models\Certificate::STATUS_SUBMITTED => 'bg-info text-dark',
              \App\Models\Certificate::STATUS_APPROVED => 'bg-primary',
              \App\Models\Certificate::STATUS_FINAL_GENERATED => 'bg-success',
              \App\Models\Certificate::STATUS_SIGNED => 'bg-success',
              \App\Models\Certificate::STATUS_REJECTED => 'bg-danger',
              default => 'bg-secondary'
            };
          @endphp

          <tr>
            <td>{{ ($participants->currentPage()-1)*$participants->perPage() + $loop->iteration }}</td>

            <td class="fw-semibold">
              {{ $p->name }}
              @if($p->institution)
                <div class="text-muted small">{{ $p->institution }}</div>
              @endif
            </td>

            <td>{{ $p->email ?? '-' }}</td>
            <td>{{ $p->event?->name ?? '-' }}</td>

            <td>
              @if($hasCert)
                <span class="badge {{ $badgeClass }}">{{ ucfirst($statusVal) }}</span>
                <div class="text-muted small mt-1">
                  {{ $cert->certificate_number ?? $cert->certificate_no ?? $cert->certificate_no ?? '-' }}
                </div>
              @else
                <span class="text-muted small">Belum ada</span>
              @endif
            </td>

            <td class="text-end">
              <div class="d-inline-flex gap-2">

                {{-- 1) Generate Draft: hanya kalau belum ada --}}
                <form method="POST" action="{{ route('admin.certificates.generateOne', $p->id) }}" class="d-inline">
                  @csrf
                  <input type="hidden" name="event_id" value="{{ $p->event_id }}">
                  <button class="btn btn-warning btn-sm rounded-3"
                          title="{{ $hasCert ? 'Draft sudah ada' : 'Generate Draft' }}"
                          {{ $hasCert ? 'disabled' : '' }}>
                    <i class="fa-solid fa-bolt"></i>
                  </button>
                </form>

                {{-- 2) Ajukan: hanya saat draft --}}
                @if($hasCert && $statusVal === \App\Models\Certificate::STATUS_DRAFT)
                  <form method="POST" action="{{ route('admin.certificates.submit', $cert->id) }}" class="d-inline">
                    @csrf
                    <button class="btn btn-primary btn-sm rounded-3" title="Ajukan ke Persetujuan">
                      <i class="fa-solid fa-paper-plane"></i>
                    </button>
                  </form>
                @else
                  <button class="btn btn-outline-secondary btn-sm rounded-3" disabled title="Ajukan hanya untuk Draft">
                    <i class="fa-solid fa-paper-plane"></i>
                  </button>
                @endif

                {{-- 3) Generate PDF Final: hanya saat approved --}}
                @if($hasCert && $statusVal === \App\Models\Certificate::STATUS_APPROVED)
                  <form method="POST" action="{{ route('admin.certificates.generatePdfOne', $cert->id) }}" class="d-inline">
                    @csrf
                    <button class="btn btn-success btn-sm rounded-3" title="Generate PDF Final">
                      <i class="fa-solid fa-file-pdf"></i>
                    </button>
                  </form>
                @else
                  <button class="btn btn-outline-secondary btn-sm rounded-3" disabled title="PDF final hanya setelah Approved">
                    <i class="fa-solid fa-file-pdf"></i>
                  </button>
                @endif

                {{-- 4) Preview --}}
                @if($hasPdf)
                  <a class="btn btn-outline-success btn-sm rounded-3"
                     href="{{ route('admin.certificates.view', $cert->id) }}"
                     target="_blank"
                     title="Preview PDF">
                    <i class="fa-solid fa-eye"></i>
                  </a>
                @else
                  <button class="btn btn-outline-secondary btn-sm rounded-3" disabled title="PDF belum ada">
                    <i class="fa-solid fa-eye"></i>
                  </button>
                @endif

                {{-- 5) Download --}}
                @if($hasPdf)
                  <a class="btn btn-outline-primary btn-sm rounded-3"
                     href="{{ route('admin.certificates.download', $cert->id) }}"
                     title="Download PDF">
                    <i class="fa-solid fa-download"></i>
                  </a>
                @else
                  <button class="btn btn-outline-secondary btn-sm rounded-3" disabled title="PDF belum ada">
                    <i class="fa-solid fa-download"></i>
                  </button>
                @endif
                
                {{-- 6) Reset/Revise: hanya saat final_generated atau signed + Cek Role --}}
                @if($hasCert && in_array($statusVal, [\App\Models\Certificate::STATUS_FINAL_GENERATED, \App\Models\Certificate::STATUS_SIGNED]) && in_array(auth()->user()->role?->name, ['admin', 'superadmin']))
                  <form method="POST" action="{{ route('admin.certificates.revise', $cert->id) }}" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin melakukan revisi? Sertifikat akan di-reset ke status Approved dan file PDF lama akan dihapus. Nomor sertifikat TETAP.')">
                    @csrf
                    <button class="btn btn-danger btn-sm rounded-3" title="Reset / Revisi (Nomor Tetap)">
                      <i class="fa-solid fa-rotate"></i>
                    </button>
                  </form>
                @endif

              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="text-center text-muted py-4">Belum ada peserta.</td>
          </tr>
        @endforelse
      @endif
      </tbody>
    </table>
  </div>

  @if($participants && $participants->hasPages())
    <div class="card-footer bg-white border-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div class="text-muted small">
        Menampilkan {{ $participants->firstItem() }} - {{ $participants->lastItem() }}
        dari {{ $participants->total() }} data
      </div>
      <div>{{ $participants->links() }}</div>
    </div>
  @endif
</div>
@endsection