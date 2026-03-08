@extends('layouts.app')
@section('title','Audit Trail')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
  <div>
    <h4 class="fw-bold mb-1">Audit Trail</h4>
    <div class="text-muted small">Riwayat aktivitas sistem dan jejak audit keamanan.</div>
  </div>
</div>

{{-- Filter Card --}}
<form method="GET" action="{{ route('admin.audit.index') }}" class="card border-0 shadow-sm rounded-4 mb-3">
    <div class="card-body p-3">
        <div class="row g-3">
            <div class="col-lg-4">
                <label class="form-label small text-muted mb-1 fw-semibold">Tipe Event</label>
                <select name="event_type" class="form-select rounded-3">
                    <option value="">-- Semua Event --</option>
                    <option value="certificate.reviewed" @selected(request('event_type') == 'certificate.reviewed')>Certificate Reviewed</option>
                    <option value="certificate.approved" @selected(request('event_type') == 'certificate.approved')>Certificate Approved</option>
                    <option value="certificate.rejected" @selected(request('event_type') == 'certificate.rejected')>Certificate Rejected</option>
                    <option value="certificate.signed" @selected(request('event_type') == 'certificate.signed')>Certificate Signed (TTE)</option>
                </select>
            </div>
            
            <div class="col-lg-2 d-flex align-items-end gap-2">
                <button class="btn btn-primary h-100 flex-grow-1 rounded-3">
                    <i class="fa-solid fa-filter me-1 small"></i> Filter
                </button>
                <a class="btn btn-outline-secondary h-100 px-3 rounded-3 d-flex align-items-center" href="{{ route('admin.audit.index') }}">
                  <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </div>
    </div>
</form>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
        <div class="fw-bold"><i class="fa-solid fa-list-check me-2 text-primary"></i>Log Aktivitas</div>
        <div class="text-muted small">Total: {{ $logs->total() }} record</div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th width="180px">Waktu</th>
                    <th>Aktor / User</th>
                    <th>Event</th>
                    <th>Subject ID</th>
                    <th>IP Address</th>
                    <th class="text-end">Detail</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td class="small text-muted">
                            <div>{{ $log->created_at->format('d M Y') }}</div>
                            <div class="fw-bold text-dark">{{ $log->created_at->format('H:i:s') }}</div>
                        </td>
                        <td>
                            @if($log->actor)
                                <div class="fw-bold">{{ $log->actor->name }}</div>
                                <div class="text-muted small">{{ $log->actor->email }}</div>
                            @else
                                <span class="text-muted small">System / Guest</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $badgeClass = match($log->event_type) {
                                    'certificate.approved' => 'bg-success bg-opacity-10 text-success border-success',
                                    'certificate.rejected' => 'bg-danger bg-opacity-10 text-danger border-danger',
                                    'certificate.signed'   => 'bg-info bg-opacity-10 text-info border-info border-opacity-25',
                                    'certificate.reviewed' => 'bg-primary bg-opacity-10 text-primary border-primary',
                                    default => 'bg-secondary bg-opacity-10 text-muted border-secondary'
                                };
                            @endphp
                            <span class="badge border {{ $badgeClass }} rounded-pill px-2 py-1 fw-medium" style="font-size: 0.7rem;">
                                {{ strtoupper(str_replace('.', ' ', $log->event_type)) }}
                            </span>
                        </td>
                        <td class="small text-muted font-monospace" style="font-size: 0.75rem;">
                            {{ $log->subject_id }}
                        </td>
                        <td class="small">
                            <code>{{ $log->actor_ip ?? '127.0.0.1' }}</code>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-secondary border-0 rounded-circle" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modal-{{ $log->id }}"
                                    style="width: 32px; height: 32px;">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            
                            {{-- Simple Modal for JSON Metadata --}}
                            <div class="modal fade text-start" id="modal-{{ $log->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow rounded-4">
                                        <div class="modal-header border-0">
                                            <h6 class="modal-title fw-bold">Detail Log</h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body bg-light rounded-bottom-4">
                                            <div class="mb-3">
                                                <label class="small text-muted fw-bold d-block mb-1">HASH VERIFICATION</label>
                                                <div class="p-2 bg-white border rounded small text-break font-monospace">
                                                    {{ $log->hash }}
                                                </div>
                                            </div>
                                            <div>
                                                <label class="small text-muted fw-bold d-block mb-1">METADATA</label>
                                                <pre class="bg-dark text-success p-3 rounded small mb-0" style="max-height: 200px; overflow: auto;">{{ json_encode($log->metadata, JSON_PRETTY_PRINT) }}</pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="text-muted"><i class="fa-solid fa-database mb-2 fs-2"></i></div>
                            <div class="fw-bold text-muted">Belum ada riwayat aktivitas.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white border-0 py-3">
        {{ $logs->links() }}
    </div>
</div>
@endsection
