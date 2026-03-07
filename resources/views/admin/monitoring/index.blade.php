@extends('layouts.app')
@section('title','Monitoring Sistem')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <div>
    <h4 class="mb-0">Monitoring Sistem & Antrean</h4>
    <div class="text-muted">Pantau proses background, queue, dan performa aplikasi.</div>
  </div>
  
  <div class="d-flex gap-2">
      <form action="{{ route('admin.monitoring.retryFailed') }}" method="POST" onsubmit="return confirm('Mencoba ulang semua proses yang gagal?')">
          @csrf
          <button type="submit" class="btn btn-outline-primary shadow-sm rounded-3"><i class="fa-solid fa-rotate-right me-1"></i> Retry Failed Jobs</button>
      </form>
      <form action="{{ route('admin.monitoring.clearFailed') }}" method="POST" onsubmit="return confirm('Menghapus semua daftar tugas yang gagal (failed jobs) secara permanen?')">
          @csrf
          <button type="submit" class="btn btn-outline-danger shadow-sm rounded-3"><i class="fa-solid fa-trash me-1"></i> Flush Failed</button>
      </form>
  </div>
</div>

@if(session('success'))
  <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

{{-- WIDGETS STATISTIK ANTRIAN --}}
<div class="row g-3 mb-4">
  <div class="col-lg-2 col-md-4">
    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
      <div class="card-body px-2">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <div class="text-muted small fw-bold text-uppercase mb-1 border-bottom pb-1">Queue Tunggu</div>
            <h3 class="mb-0 fw-bold {{ $pendingJobs > 0 ? 'text-warning' : 'text-dark' }}">{{ number_format($pendingJobs) }}</h3>
          </div>
          <div class="rounded-circle bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size: 18px;">
            <i class="fa-solid fa-hourglass-half fa-spin-pulse"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-lg-2 col-md-4">
    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
      <div class="card-body px-2">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <div class="text-muted small fw-bold text-uppercase mb-1 border-bottom pb-1">Queue Gagal</div>
            <h3 class="mb-0 fw-bold {{ $failedJobs > 0 ? 'text-danger' : 'text-dark' }}">{{ number_format($failedJobs) }}</h3>
          </div>
          <div class="rounded-circle bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size: 18px;">
            <i class="fa-solid fa-triangle-exclamation"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-2 col-md-4">
    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white border-bottom border-4 border-success">
      <div class="card-body px-2">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <div class="text-muted small fw-bold text-uppercase mb-1 border-bottom pb-1">PDF Cetak</div>
            <h3 class="mb-0 fw-bold text-success">{{ number_format($totalCertificatesGenerations) }}</h3>
          </div>
          <div class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size: 18px;">
            <i class="fa-regular fa-file-pdf"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-2 col-md-4">
    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white border-bottom border-4 border-warning">
      <div class="card-body px-2">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <div class="text-muted small fw-bold text-uppercase mb-1 border-bottom pb-1">Antrian TTE</div>
            <h3 class="mb-0 fw-bold text-warning">{{ number_format($pendingTte) }}</h3>
          </div>
          <div class="rounded-circle bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size: 18px;">
            <i class="fa-solid fa-list-check"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-2 col-md-4">
    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white border-bottom border-4 border-primary">
      <div class="card-body px-2">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <div class="text-muted small fw-bold text-uppercase mb-1 border-bottom pb-1">TTE SIgned</div>
            <h3 class="mb-0 fw-bold text-primary">{{ number_format($totalTteSigned) }}</h3>
          </div>
          <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size: 18px;">
            <i class="fa-solid fa-signature"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-2 col-md-4">
    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white border-bottom border-4 border-info">
      <div class="card-body px-2">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <div class="text-muted small fw-bold text-uppercase mb-1 border-bottom pb-1">No. Terakhir</div>
            <div class="fw-bold text-info text-truncate small" style="max-width: 130px;" title="{{ $lastNumber }}">{{ $lastNumber }}</div>
          </div>
          <div class="rounded-circle bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size: 18px;">
            <i class="fa-solid fa-list-ol"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4">
    <!-- PENDING JOBS -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white border-bottom-0 py-3">
                <h6 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-server ms-1 me-2 text-warning"></i>Proses Berjalan (Pending Jobs)</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 font-monospace" style="font-size: 13px;">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">ID</th>
                            <th>Job Class</th>
                            <th>Dimasukkan</th>
                            <th class="text-center">Percobaan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentJobs as $job)
                        @php
                            $className = '-';
                            if (isset($job->payload->displayName)) $className = class_basename($job->payload->displayName);
                            if (isset($job->payload->data->commandName)) $className = class_basename($job->payload->data->commandName);
                        @endphp
                        <tr>
                            <td class="text-muted">#{{ $job->id }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $className }}</span></td>
                            <td>{{ \Carbon\Carbon::parse($job->created_at)->diffForHumans() }}</td>
                            <td class="text-center">{{ $job->attempts }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-check-double fs-1 mb-2 text-success opacity-50"></i><br>
                                Tidak antrean yang harus dikerjakan server saat ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-top small text-muted text-center">
                *Tabel ini hanya menampilkan maks 10 antrean pertama (FIFO)
            </div>
        </div>
    </div>

    <!-- FAILED JOBS -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white border-bottom-0 py-3">
                <h6 class="mb-0 fw-bold text-danger"><i class="fa-solid fa-bug ms-1 me-2 text-danger"></i>Log Kegagalan (Failed Jobs)</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 font-monospace" style="font-size: 13px;">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">ID</th>
                            <th>Job Class</th>
                            <th>Waktu Gagal</th>
                            <th width="40%">Error Hint</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentFailedJobs as $fail)
                        @php
                            $payload = json_decode($fail->payload);
                            $className = '-';
                            if (isset($payload->displayName)) $className = class_basename($payload->displayName);
                            if (isset($payload->data->commandName)) $className = class_basename($payload->data->commandName);
                            
                            // Ambil sedikit pesan error dari exception
                            $lines = explode("\n", $fail->exception);
                            $errorHint = $lines[0] ?? '-';
                        @endphp
                        <tr>
                            <td class="text-muted">#{{ $fail->id }}</td>
                            <td><span class="badge bg-danger bg-opacity-10 text-danger border border-danger">{{ $className }}</span></td>
                            <td>{{ \Carbon\Carbon::parse($fail->failed_at)->diffForHumans() }}</td>
                            <td>
                                <div class="text-truncate text-danger" style="max-width: 200px;" title="{{ $errorHint }}">
                                    {{ $errorHint }}
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-mug-hot fs-1 mb-2 text-secondary opacity-50"></i><br>
                                Bersih! Tidak ada *error* membandel.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-top small text-muted text-center">
                *Tabel ini menampilkan log error gagal yang terbaru.
            </div>
        </div>
    </div>
</div>
@endsection
