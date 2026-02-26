{{-- resources/views/admin/tte/signing/index.blade.php --}}
@extends('layouts.app')
@section('title','Signing Queue')

@section('content')
<div class="d-flex justify-content-between align-items-start mb-3">
  <div>
    <h4 class="mb-0">Signing Queue</h4>
    <div class="text-muted">Daftar sertifikat status <b>APPROVED</b> / <b>FINAL_GENERATED</b> yang siap ditandatangani.</div>
  </div>
</div>

@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
  <div class="alert alert-danger">{{ session('error') }}</div>
@endif

{{-- Filter --}}
<form class="card p-3 mb-3" method="GET" action="{{ route('admin.tte.signing.index') }}">
  <div class="row g-3 align-items-end">
    <div class="col-md-6">
      <label class="form-label">Cari</label>
      <input type="text" class="form-control" name="q" value="{{ $q ?? '' }}" placeholder="Cari no sertifikat / nama peserta...">
    </div>
    <div class="col-md-4">
      <label class="form-label">Event</label>
      <select name="event_id" class="form-select">
        <option value="">-- Semua Event --</option>
        @foreach(($events ?? collect()) as $e)
          <option value="{{ $e->id }}" @selected(($eventId ?? null) == $e->id)>{{ $e->name }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-2 text-end">
      <button class="btn btn-primary">Cari</button>
      <a href="{{ route('admin.tte.signing.index') }}" class="btn btn-outline-secondary">Reset</a>
    </div>
  </div>
</form>

{{-- Dispatch panel (Bulk) --}}
<form method="POST" action="{{ route('admin.tte.signing.dispatchBulk') }}" class="card p-3 mb-3">
  @csrf

  <div class="row g-3 align-items-end">
    <div class="col-md-6">
      <label class="form-label">Signer untuk dispatch</label>
     <select name="signer_cert_code" class="form-select" required>
      <option value="">Pilih signer...</option>
      @foreach(($signers ?? collect()) as $s)
        <option value="{{ $s->code }}">{{ $s->name }} ({{ $s->code }})</option>
      @endforeach
    </select>
      @error('signer_certificate_id')
        <div class="text-danger small mt-1">{{ $message }}</div>
      @enderror
    </div>

    <div class="col-md-6">
      <label class="form-label">Visibility</label>
      <div class="d-flex gap-4">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="barcode_visible" value="1" id="barcode_visible" checked>
          <label class="form-check-label" for="barcode_visible">Tampilkan Barcode</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="tte_visible" value="1" id="tte_visible" checked>
          <label class="form-check-label" for="tte_visible">Tampilkan TTE</label>
        </div>
      </div>
    </div>

    <div class="col-md-2">
      <label class="form-label">Page</label>
      <input type="number" class="form-control" name="appearance_page" min="1" value="1">
    </div>
    <div class="col-md-2">
      <label class="form-label">X</label>
      <input type="number" class="form-control" name="appearance_x" min="0" value="0">
    </div>
    <div class="col-md-2">
      <label class="form-label">Y</label>
      <input type="number" class="form-control" name="appearance_y" min="0" value="0">
    </div>
    <div class="col-md-2">
      <label class="form-label">W</label>
      <input type="number" class="form-control" name="appearance_w" min="1" value="200">
    </div>
    <div class="col-md-2">
      <label class="form-label">H</label>
      <input type="number" class="form-control" name="appearance_h" min="1" value="80">
    </div>

    <div class="col-md-2 text-end">
      <button type="submit" class="btn btn-success">Dispatch Sign (Bulk)</button>
      <div class="small text-muted mt-1">Max 20 data</div>
    </div>
  </div>

  <div class="table-responsive mt-3">
    <table class="table align-middle">
      <thead>
        <tr>
          <th style="width:40px;">
            <input type="checkbox" id="checkAll">
          </th>
          <th>No Sertifikat</th>
          <th>Nama Peserta</th>
          <th>Event</th>
          <th>Status</th>
          <th class="text-end">Action</th>
        </tr>
      </thead>

      <tbody>
        @forelse(($certificates ?? collect()) as $c)
          <tr>
            {{-- checkbox bulk --}}
            <td>
              <input type="checkbox" name="certificate_ids[]" value="{{ $c->id }}" class="rowCheck">
            </td>

            <td>{{ $c->certificate_number ?? $c->certificate_no ?? '-' }}</td>
            <td>{{ $c->participant?->name ?? '-' }}</td>
            <td>{{ $c->event?->name ?? '-' }}</td>
            <td>
              <span class="badge bg-success">{{ strtoupper((string) $c->status) }}</span>
            </td>

            {{-- Actions --}}
            <td class="text-end">
              <div class="d-inline-flex gap-2 justify-content-end">
                {{-- Preview PDF --}}
               <a href="{{ route('admin.certificates.view', $c->id) }}"
   class="btn btn-outline-secondary btn-sm"
   target="_blank">
  Preview
</a>

                {{-- Dispatch per-row --}}
               <form method="POST"
      action="{{ route('admin.tte.signing.signNow', $c->id) }}"
      class="d-inline singleDispatchForm">
  @csrf

  <input type="hidden" name="signer_cert_code" value=""> {{-- akan diisi via JS --}}
  <input type="hidden" name="appearance_mode" value="visible">
  <input type="hidden" name="appearance_page" value="1">
  <input type="hidden" name="appearance_x" value="0">
  <input type="hidden" name="appearance_y" value="0">
  <input type="hidden" name="appearance_w" value="200">
  <input type="hidden" name="appearance_h" value="80">

  <button type="submit" class="btn btn-primary btn-sm">Dispatch Sign</button>
</form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="text-center text-muted py-3">Tidak ada data.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</form>

@if(isset($certificates) && method_exists($certificates, 'links'))
  <div class="mt-3">
    {{ $certificates->links() }}
  </div>
@endif

<script>
document.addEventListener('DOMContentLoaded', () => {
  const checkAll = document.getElementById('checkAll');
  const rowChecks = document.querySelectorAll('.rowCheck');

  if (checkAll) {
    checkAll.addEventListener('change', () => {
      rowChecks.forEach(ch => ch.checked = checkAll.checked);
    });
  }

  const signerSelect = document.querySelector('select[name="signer_certificate_id"]');
  const barcodeVisible = document.getElementById('barcode_visible');
  const tteVisible = document.getElementById('tte_visible');

  const page = document.querySelector('input[name="appearance_page"]');
  const x = document.querySelector('input[name="appearance_x"]');
  const y = document.querySelector('input[name="appearance_y"]');
  const w = document.querySelector('input[name="appearance_w"]');
  const h = document.querySelector('input[name="appearance_h"]');

  document.querySelectorAll('.singleDispatchForm').forEach(form => {
    form.addEventListener('submit', (e) => {
      if (!signerSelect || !signerSelect.value) {
        e.preventDefault();
        alert('Pilih signer terlebih dahulu.');
        return;
      }

      form.querySelector('input[name="signer_cert_code"]').value = signerSelect.value;
      Visible.checked) ? '1' : '0';
      form.querySelector('input[name="tte_visible"]').value = (tteVisible && tteVisible.checked) ? '1' : '0';

      form.querySelector('input[name="appearance_page"]').value = (page && page.value) ? page.value : '1';
      form.querySelector('input[name="appearance_x"]').value = (x && x.value) ? x.value : '0';
      form.querySelector('input[name="appearance_y"]').value = (y && y.value) ? y.value : '0';
      form.querySelector('input[name="appearance_w"]').value = (w && w.value) ? w.value : '200';
      form.querySelector('input[name="appearance_h"]').value = (h && h.value) ? h.value : '80';
    });
  });
});
</script>
@endsection