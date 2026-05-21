@extends('layouts.app')
@section('title', 'Distribusi Email')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold"><i class="fa-solid fa-paper-plane me-2 text-primary"></i>Distribusi Email</h4>
        <div class="text-muted mt-1">Kirim sertifikat yang telah diterbitkan (TTE) ke email peserta.</div>
    </div>
</div>

{{-- ALERT SUCCESS/ERROR --}}
@if (session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fa-solid fa-circle-check me-1"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if ($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <div class="fw-bold"><i class="fa-solid fa-triangle-exclamation me-1"></i> Gagal Mengirim Email</div>
    <ul class="mb-0 mt-2">
        @foreach ($errors->all() as $err)
            <li>{{ $err }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white py-3">
        <form method="GET" action="{{ route('admin.emails.index') }}" class="row g-2 align-items-center">
            
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari nama / email..." value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-md-3">
                <select name="event_id" class="form-select">
                    <option value="">Semua Event / Program</option>
                    @foreach ($events as $event)
                        <option value="{{ $event->id }}" @selected(request('event_id') == $event->id)>{{ $event->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="signed" @selected(request('status') === 'signed')>TTE Selesai</option>
                    <option value="terbit" @selected(request('status') === 'terbit')>Telah Terbit</option>
                    <option value="sent" @selected(request('status') === 'sent')>Sudah Dikirim</option>
                </select>
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary px-3 text-nowrap"><i class="fa-solid fa-filter me-1"></i> Filter</button>
                @if(request()->hasAny(['search', 'status', 'event_id']))
                    <a href="{{ route('admin.emails.index') }}" class="btn btn-light border text-nowrap">Reset</a>
                @endif
            </div>

        </form>
    </div>

    <div class="card-body p-0 table-responsive border-top">
        <form id="formSendEmail" method="POST" action="{{ route('admin.emails.send') }}">
            @csrf
            
            <div class="px-4 py-3 bg-light d-flex justify-content-between align-items-center">
                <span class="text-secondary fw-semibold">
                    <span id="selectedCount">0</span> Sertifikat dipilih
                </span>
                <button type="button" class="btn btn-success" onclick="confirmSendEmail()" id="btnSend" disabled>
                    <i class="fa-solid fa-paper-plane me-1"></i> Kirim Email Terpilih
                </button>
            </div>

            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="40" class="text-center ps-4">
                            <input class="form-check-input" type="checkbox" id="checkAll">
                        </th>
                        <th>Peserta</th>
                        <th>Program / Event</th>
                        <th>Sertifikat</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($certificates as $cert)
                    <tr>
                        <td class="text-center ps-4">
                            <input class="form-check-input row-checkbox" type="checkbox" name="certificate_ids[]" value="{{ $cert->id }}">
                        </td>
                        <td>
                            <div class="fw-bold">{{ $cert->participant->name ?? '-' }}</div>
                            <div class="small text-muted"><i class="fa-regular fa-envelope me-1"></i>{{ $cert->participant->email ?? 'Tidak ada email' }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold text-truncate" style="max-width:250px;" title="{{ $cert->event->name ?? '-' }}">{{ $cert->event->name ?? '-' }}</div>
                        </td>
                        <td>
                            <div class="font-monospace small bg-light p-1 rounded d-inline-block">{{ $cert->certificate_number ?? '-' }}</div>
                        </td>
                        <td>
                            @if ($cert->status === \App\Models\Certificate::STATUS_SENT)
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">
                                    <i class="fa-solid fa-check-double me-1"></i>Terkirim
                                </span>
                                @if($cert->sent_at)
                                    <div class="small text-muted mt-1" style="font-size:11px;">{{ $cert->sent_at->format('d M Y, H:i') }}</div>
                                @endif
                            @else
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1">
                                    <i class="fa-solid fa-envelope-circle-check me-1"></i>Siap Kirim
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="text-muted"><i class="fa-regular fa-folder-open fs-2 mb-2"></i><br>Tidak ada sertifikat siap kirim yang ditemukan.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </form>
    </div>
    
    @if ($certificates->hasPages())
    <div class="card-footer bg-white py-3">
        {{ $certificates->links() }}
    </div>
    @endif
</div>

<script>
    const checkAll = document.getElementById('checkAll');
    const checkboxes = document.querySelectorAll('.row-checkbox');
    const selectedCount = document.getElementById('selectedCount');
    const btnSend = document.getElementById('btnSend');

    function updateSelection() {
        const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
        const count = checkedBoxes.length;
        selectedCount.textContent = count;
        
        btnSend.disabled = count === 0;
        checkAll.checked = (count === checkboxes.length && checkboxes.length > 0);
    }

    if (checkAll) {
        checkAll.addEventListener('change', function() {
            checkboxes.forEach(cb => {
                cb.checked = this.checked;
            });
            updateSelection();
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateSelection);
    });

    function confirmSendEmail() {
        const count = document.querySelectorAll('.row-checkbox:checked').length;
        if(count === 0) return;
        
        if(confirm(`Anda yakin ingin mengirim / mensimulasikan pengiriman email ke ${count} peserta terpilih?`)) {
            document.getElementById('formSendEmail').submit();
        }
    }
</script>
@endsection
