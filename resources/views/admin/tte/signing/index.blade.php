{{-- resources/views/admin/tte/signing/index.blade.php --}}
@extends('layouts.app')
@section('title','Signing Queue')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="mb-0 fw-bold">Signing Queue</h4>
    <div class="text-muted">Daftar sertifikat status <b>FINAL_GENERATED</b> atau <b>GAGAL_TTE</b> yang siap untuk dibubuhi Tanda Tangan Elektronik.</div>
  </div>
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
@if($errors->any())
  <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
    <div class="fw-bold mb-1"><i class="fa-solid fa-triangle-exclamation me-1"></i> Validasi Gagal:</div>
    <ul class="mb-0 small">
      @foreach($errors->all() as $error)
         <li>{{ $error }}</li>
      @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

{{-- FILTER CARD --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
  <div class="card-body">
    <form method="GET" action="{{ route('admin.tte.signing.index') }}" class="row g-3 align-items-end">
      <div class="col-md-5">
        <label class="form-label small text-muted mb-1">Pencarian</label>
        <input type="text" class="form-control" name="q" value="{{ $q ?? '' }}" placeholder="Cari No. sertifikat atau nama peserta...">
      </div>
      <div class="col-md-5">
        <label class="form-label small text-muted mb-1">Filter Event</label>
        <select name="event_id" class="form-select">
          <option value="">-- Semua Event --</option>
          @foreach(($events ?? collect()) as $e)
            <option value="{{ $e->id }}" @selected(($eventId ?? null) == $e->id)>{{ $e->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2 d-flex gap-2">
        <button class="btn btn-primary w-100"><i class="fa-solid fa-magnifying-glass"></i> Cari</button>
        <a href="{{ route('admin.tte.signing.index') }}" class="btn btn-outline-secondary" title="Reset filter"><i class="fa-solid fa-rotate-left"></i></a>
      </div>
    </form>
  </div>
</div>

{{-- KONFIGURASI SIGNER & TTE CARD --}}
<div class="card border-primary border-opacity-25 shadow-sm rounded-4 mb-4">
  <div class="card-header bg-primary bg-opacity-10 border-bottom border-primary border-opacity-25 p-3">
    <h6 class="mb-0 fw-bold text-primary"><i class="fa-solid fa-pen-nib me-2"></i>Konfigurasi Penanda Tangan (Signer)</h6>
  </div>
  <div class="card-body">
    <div class="row g-4 align-items-center">
      <div class="col-md-5">
        <label class="form-label small text-muted fw-bold mb-1">Pilih Signer (Penandatangan) <span class="text-danger">*</span></label>
        <select id="globalSignerSelect" class="form-select border-primary shadow-sm" required>
          <option value="">-- Wajib Pilih Signer --</option>
          @foreach(($signers ?? collect()) as $s)
            @php
              // Set Dr. Jarwoko sebagai default selected
              $isJarwoko = str_contains(strtolower($s->name), 'jarwoko');
            @endphp
            <option value="{{ $s->id }}" @selected($isJarwoko)>{{ $s->name }} (Kode: {{ $s->code }})</option>
          @endforeach
        </select>
        <div class="text-muted small mt-1"><i class="fa-solid fa-circle-info me-1"></i>Pilih siapa yang akan menandatangani dokumen-dokumen di bawah ini secara massal.</div>
      </div>
      
      <div class="col-md-7 border-start">
         <label class="form-label small text-muted fw-bold mb-2">Penyesuaian Visual TTE (Multi Halaman)</label>
         <div id="placementContainer">
             <div class="placement-row d-flex align-items-center gap-2 mb-2 flex-wrap bg-light p-2 rounded-3 border">
                <div class="input-group input-group-sm shadow-sm" style="width: 85px;" title="Halaman Posisi TTE">
                  <span class="input-group-text bg-white border-0">Hal</span>
                  <input type="number" class="form-control border-light placement-page" value="1" min="1">
                </div>
                <div class="input-group input-group-sm shadow-sm" style="width: 75px;" title="Titik X (Horizontal mm)">
                  <input type="number" class="form-control border-light placement-x" value="20" placeholder="X">
                </div>
                <div class="input-group input-group-sm shadow-sm" style="width: 75px;" title="Titik Y (Vertikal mm)">
                  <input type="number" class="form-control border-light placement-y" value="160" placeholder="Y">
                </div>
                <div class="input-group input-group-sm shadow-sm" style="width: 85px;" title="Lebar Area TTE (mm)">
                  <span class="input-group-text bg-white border-0">W</span>
                  <input type="number" class="form-control border-light placement-w" value="35">
                </div>
                <div class="input-group input-group-sm shadow-sm" style="width: 85px;" title="Tinggi Area TTE (mm)">
                  <span class="input-group-text bg-white border-0">H</span>
                  <input type="number" class="form-control border-light placement-h" value="35">
                </div>
                <div class="form-check form-switch ms-2">
                  <input class="form-check-input placement-barcode" type="checkbox" checked>
                  <label class="form-check-label x-small">QR</label>
                </div>
                <div class="form-check form-switch ms-1">
                  <input class="form-check-input placement-tte" type="checkbox" checked>
                  <label class="form-check-label x-small">Teks</label>
                </div>
             </div>
         </div>
         <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="btnAddPlacement">
             <i class="fa-solid fa-plus me-1"></i> Tambah Lokasi Sign (Halaman Lain)
         </button>
      </div>
    </div>
  </div>
</div>

{{-- DAFTAR SERTIFIKAT CARD --}}
<div class="card border-0 shadow-sm rounded-4">
  <div class="card-header bg-white border-bottom p-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
      <h6 class="mb-0 fw-bold">Daftar Antrean Sertifikat</h6>
      <span class="text-muted small">Tandai sertifikat di bawah ini, pastikan Konfigurasi Signer di atas telah dipilih, lalu klik Dispatch.</span>
    </div>
    <button type="button" class="btn btn-success rounded-pill px-4 shadow-sm" style="font-weight: 600;" id="btnBulkDispatch">
      <i class="fa-solid fa-layer-group me-1"></i> Dispatch Terpilih (Bulk)
    </button>
  </div>
  
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th width="5%" class="text-center">
            <input class="form-check-input shadow-sm" type="checkbox" id="checkAll">
          </th>
          <th width="20%">No Sertifikat</th>
          <th>Nama Peserta</th>
          <th width="30%">Event</th>
          <th>Status</th>
          <th class="text-end" width="15%">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse(($certificates ?? collect()) as $c)
          <tr>
            <td class="text-center py-3">
              @if($c->status !== 'scheduled')
                <input class="form-check-input rowCheck shadow-sm" type="checkbox" value="{{ $c->id }}">
              @else
                <i class="fa-solid fa-clock text-warning" title="Dijadwalkan"></i>
              @endif
            </td>
            <td class="py-3">
               <div class="fw-semibold text-primary" style="font-size: 0.9em;">
                 {{ $c->certificate_number ?? $c->certificate_no ?? '-' }}
               </div>
            </td>
            <td class="py-3">
              <div class="fw-bold text-dark">{{ $c->participant?->name ?? '-' }}</div>
            </td>
            <td class="py-3">
              <div class="small text-muted">{{ $c->event?->name ?? '-' }}</div>
            </td>
            <td class="py-3">
              @php
                $status = strtolower((string)$c->status);
                $isFailed = $status === 'gagal_tte';
                $isScheduled = $status === 'scheduled';
                
                $badgeClass = 'bg-success';
                if ($isFailed) $badgeClass = 'bg-danger';
                if ($isScheduled) $badgeClass = 'bg-warning';
              @endphp
              <span class="badge {{ $badgeClass }} bg-opacity-10 {{ $isScheduled ? 'text-warning-emphasis' : ($isFailed ? 'text-danger' : 'text-success') }} border {{ $isFailed ? 'border-danger' : ($isScheduled ? 'border-warning' : 'border-success') }}-subtle rounded-pill">
                {{ strtoupper((string) $c->status) }}
              </span>
            </td>
            <td class="text-end py-3">
              <div class="d-inline-flex gap-2 justify-content-end">
                <a href="{{ route('admin.tte.signing.preview', $c->id) }}"
                   class="btn btn-light btn-sm rounded-3 border bg-white shadow-sm"
                   target="_blank" title="Cek Dokumen (Preview PDF)">
                   <i class="fa-solid fa-eye text-secondary"></i>
                </a>

                @if(!$isScheduled)
                  <button type="button" class="btn btn-primary btn-sm rounded-3 shadow-sm btnSingleDispatch fw-semibold" data-id="{{ $c->id }}" title="Langsung bubuhkan Signer ke sertifikat ini">
                    Dispatch Sign
                  </button>
                @else
                  <button type="button" class="btn btn-outline-warning btn-sm rounded-3 shadow-sm fw-semibold" disabled>
                    Queued...
                  </button>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="text-center py-5">
              <i class="fa-solid fa-inbox text-muted fs-1 mb-3 opacity-25"></i>
              <h5 class="text-muted fw-bold">Belum Ada Antrean</h5>
              <p class="text-muted small mb-0">Generasi secara final PDF setidaknya 1 sertifikat agar tampil di antrean TTE.</p>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  
  @if(isset($certificates) && method_exists($certificates, 'links'))
    <div class="card-footer bg-white border-top p-3">
      {{ $certificates->links() }}
    </div>
  @endif
</div>

{{-- Hidden Core Form to Submit Safely (No Nested forms allowed in HTML!) --}}
<form id="dispatchForm" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="signer_certificate_id" id="formSigner">
    <input type="hidden" name="barcode_visible" id="formBarcode">
    <input type="hidden" name="tte_visible" id="formTte">
    <input type="hidden" name="appearance_page" id="formPage">
    <input type="hidden" name="appearance_x" id="formX">
    <input type="hidden" name="appearance_y" id="formY">
    <input type="hidden" name="appearance_w" id="formW">
    <input type="hidden" name="appearance_h" id="formH">
    <div id="formCertificatesIds"></div>
    <div id="formPlacements"></div>
</form>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const checkAll = document.getElementById('checkAll');
    const rowChecks = document.querySelectorAll('.rowCheck');
    
    // Toggle all checkboxes
    if (checkAll) {
        checkAll.addEventListener('change', () => {
            rowChecks.forEach(ch => ch.checked = checkAll.checked);
        });
    }

    // Capture Config Panel Values
    const configSigner = document.getElementById('globalSignerSelect');
    const configBarcode = document.getElementById('globalBarcode');
    const configTte = document.getElementById('globalTte');
    const configPage = document.getElementById('globalPage');
    const configX = document.getElementById('globalX');
    const configY = document.getElementById('globalY');
    const configW = document.getElementById('globalW');
    const configH = document.getElementById('globalH');

    // Hidden form element references
    const dispatchForm = document.getElementById('dispatchForm');
    const formSigner = document.getElementById('formSigner');
    const formBarcode = document.getElementById('formBarcode');
    const formTte = document.getElementById('formTte');
    const formPage = document.getElementById('formPage');
    const formX = document.getElementById('formX');
    const formY = document.getElementById('formY');
    const formW = document.getElementById('formW');
    const formH = document.getElementById('formH');
    const formCertificatesIds = document.getElementById('formCertificatesIds');
    const formPlacements = document.getElementById('formPlacements');

    // Method to safely grab values to the hidden form
    const populateVariables = () => {
        if (!configSigner.value) {
            configSigner.classList.add('is-invalid');
            alert('⚠️ HARAP DIPERHATIKAN:\nAnda harus memilih [Signer (Penanda Tangan)] pada panel konfigurasi di atas terlebih dahulu!');
            configSigner.focus();
            return false;
        }
        configSigner.classList.remove('is-invalid');

        formSigner.value = configSigner.value;
        
        // Handle Multiple Placements
        formPlacements.innerHTML = '';
        const placementRows = document.querySelectorAll('.placement-row');
        placementRows.forEach((row, index) => {
            const data = {
                page: row.querySelector('.placement-page').value,
                x: row.querySelector('.placement-x').value,
                y: row.querySelector('.placement-y').value,
                w: row.querySelector('.placement-w').value,
                h: row.querySelector('.placement-h').value,
                barcode_visible: row.querySelector('.placement-barcode').checked ? 1 : 0,
                tte_visible: row.querySelector('.placement-tte').checked ? 1 : 0
            };
            
            Object.keys(data).forEach(key => {
                let input = document.createElement('input');
                input.type = 'hidden';
                input.name = `placements[${index}][${key}]`;
                input.value = data[key];
                formPlacements.appendChild(input);
            });
        });
        
        return true;
    };

    // Add another placement row
    document.getElementById('btnAddPlacement').addEventListener('click', () => {
        const container = document.getElementById('placementContainer');
        const firstRow = container.querySelector('.placement-row');
        const newRow = firstRow.cloneNode(true);
        
        // Reset values for better UX (e.g. assume page 2 if adding second row)
        const rows = container.querySelectorAll('.placement-row');
        newRow.querySelector('.placement-page').value = rows.length + 1;
        
        // Add delete button
        const delBtn = document.createElement('button');
        delBtn.type = 'button';
        delBtn.className = 'btn btn-outline-danger btn-sm px-2 border-0';
        delBtn.innerHTML = '<i class="fa-solid fa-trash"></i>';
        delBtn.onclick = () => newRow.remove();
        newRow.appendChild(delBtn);
        
        container.appendChild(newRow);
    });

    // Single Button Dispatch Request Linker
    document.querySelectorAll('.btnSingleDispatch').forEach(btn => {
        btn.addEventListener('click', () => {
            if (!populateVariables()) return;
            
            formCertificatesIds.innerHTML = ''; // Wipe Bulk IDs array
            
            // Alter Form action dynamically to use Single Path
            let certId = btn.getAttribute('data-id');
            // Assuming the route looks like 'admin/tte/signing/{id}/dispatch'
            dispatchForm.action = '{{ url('admin/tte/signing') }}/' + certId + '/dispatch';
            dispatchForm.submit();
            
            // Set Loading state
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-secondary');
            btn.disabled = true;
        });
    });

    // Mass Bulk Row Multi-Select Dispatch Linker
    const btnBulk = document.getElementById('btnBulkDispatch');
    if(btnBulk) {
        btnBulk.addEventListener('click', () => {
            let selectedIds = [];
            rowChecks.forEach(ch => {
                if (ch.checked) selectedIds.push(ch.value);
            });

            if (selectedIds.length === 0) {
                alert('⚠️ Anda belum menandai (mencentang) sertifikat apa pun di tabel bawah.');
                return;
            }

            if (selectedIds.length > 20) {
                alert('Maksimal pengiriman adalah 20 sertifikat dalam 1x klik eksekusi TTE. Saat ini Anda memilih ' + selectedIds.length + ' data.');
                return;
            }

            if (!populateVariables()) return;

            // Send IDs into Array to The Hidden form Element
            formCertificatesIds.innerHTML = '';
            selectedIds.forEach(id => {
                let input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'certificate_ids[]';
                input.value = id;
                formCertificatesIds.appendChild(input);
            });

            // Alter Form action dynamically to use Bulk Path Route
            dispatchForm.action = '{{ route('admin.tte.signing.dispatchBulk') }}';
            dispatchForm.submit();
            
            // Set Loading State
            btnBulk.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Mengeksekusi Antrean...';
            btnBulk.disabled = true;
        });
    }
});
</script>
@endsection