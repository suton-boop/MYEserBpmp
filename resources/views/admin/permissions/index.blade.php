@extends('layouts.app')
@section('title','Kelola Permission')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h4 class="mb-0">Kelola Permission</h4>
    <div class="text-muted">Daftar hak akses sistem (dikelompokkan per modul).</div>
  </div>

  <span class="badge text-bg-light border">
    Total: {{ $permissions->count() }}
  </span>
</div>

@php
  // helper label friendly
  $labels = [
    'dashboard-read'      => 'Lihat Dashboard',

    'event-manage'        => 'Kelola Event',
    'participant-manage'  => 'Kelola Peserta',
    'template-manage'     => 'Kelola Template Sertifikat',

    'certificate-generate'=> 'Generate Sertifikat',
    'certificate-send'    => 'Kirim Sertifikat via Email',
    'certificate-approve' => 'Persetujuan Sertifikat',

    'tte-manage'          => 'Kelola TTE (Tanda Tangan Elektronik)',
    'monitoring-read'     => 'Lihat Monitoring',
    'audit-read'          => 'Lihat Audit Trail',

    'user-manage'         => 'Kelola User',
    'role-manage'         => 'Kelola Role',
    'permission-manage'   => 'Kelola Permission',
  ];

  // mapping group berdasarkan prefix/kategori
  $groups = [
    'Dashboard' => ['dashboard-read'],
    'Event & Peserta' => ['event-manage','participant-manage'],
    'Sertifikat' => ['template-manage','certificate-generate','certificate-send','certificate-approve'],
    'TTE & Monitoring' => ['tte-manage','monitoring-read','audit-read'],
    'Manajemen Sistem' => ['user-manage','role-manage','permission-manage'],
  ];

  // buat lookup cepat: name => Permission model
  $permMap = $permissions->keyBy('name');
@endphp

<div class="row g-3">
  @foreach($groups as $groupName => $permNames)
    <div class="col-12 col-lg-6">
      <div class="card border-0 shadow-sm rounded-4 h-100">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
          <div class="fw-semibold">{{ $groupName }}</div>
          <span class="badge text-bg-light border">
            {{ collect($permNames)->filter(fn($n)=>$permMap->has($n))->count() }}
          </span>
        </div>

        <div class="card-body">
          <div class="row g-2">
            @foreach($permNames as $name)
              @php
                $p = $permMap->get($name);
              @endphp

              @if($p)
                <div class="col-12 col-md-6">
                  <div class="border rounded-3 p-2 d-flex align-items-start gap-2">
                    <div class="mt-1">
                      <i class="fa-solid fa-key text-primary"></i>
                    </div>
                    <div class="flex-grow-1">
                      <div class="fw-semibold">{{ $labels[$name] ?? $name }}</div>
                      <div class="text-muted small">{{ $name }}</div>
                    </div>
                  </div>
                </div>
              @endif
            @endforeach
          </div>
        </div>

      </div>
    </div>
  @endforeach
</div>

{{-- Jika ada permission yang tidak masuk mapping, tampilkan di bawah --}}
@php
  $known = collect($groups)->flatten()->unique();
  $unknown = $permissions->filter(fn($p)=> !$known->contains($p->name));
@endphp

@if($unknown->count())
  <div class="card border-0 shadow-sm rounded-4 mt-3">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <div class="fw-semibold">Lainnya</div>
      <span class="badge text-bg-light border">{{ $unknown->count() }}</span>
    </div>
    <div class="card-body">
      <div class="row g-2">
        @foreach($unknown as $p)
          <div class="col-12 col-md-6 col-lg-4">
            <div class="border rounded-3 p-2">
              <div class="fw-semibold">{{ $p->name }}</div>
              <div class="text-muted small">Permission belum dikelompokkan.</div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>
@endif
@endsection
