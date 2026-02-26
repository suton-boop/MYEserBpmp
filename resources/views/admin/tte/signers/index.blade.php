@extends('layouts.app')
@section('title', 'Signer Certificates')

@section('content')
<div class="d-flex justify-content-between align-items-start mb-3">
  <div>
    <h4 class="mb-0">Signer Certificates</h4>
    <div class="text-muted">Kelola kunci RSA (encrypted at rest) untuk penandatangan.</div>
  </div>
  <a class="btn btn-primary" href="{{ route('admin.tte.signers.create') }}">Tambah Signer</a>
</div>

@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
  <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card rounded-3">
  <div class="table-responsive">
    <table class="table table-striped mb-0 align-middle">
      <thead>
        <tr>
          <th>Code</th>
          <th>Name</th>
          <th>Fingerprint</th>
          <th>Status</th>
          <th>Valid</th>
          <th class="text-end">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($items as $it)
          <tr>
            <td class="fw-semibold">{{ $it->code }}</td>
            <td>{{ $it->name }}</td>
            <td><code>{{ substr($it->private_key_fingerprint,0,20) }}…</code></td>
            <td>
              @if($it->is_active)
                <span class="badge bg-success">Active</span>
              @else
                <span class="badge bg-secondary">Inactive</span>
              @endif
            </td>
            <td class="text-muted">
              {{ $it->valid_from?->format('Y-m-d') ?? '-' }} → {{ $it->valid_to?->format('Y-m-d') ?? '-' }}
            </td>
            <td class="text-end">
              @if($it->is_active)
                <form method="POST" action="{{ route('admin.tte.signers.deactivate', $it->id) }}" class="d-inline">
                  @csrf
                  <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Nonaktifkan signer ini?')">
                    Deactivate
                  </button>
                </form>
              @else
                <form method="POST" action="{{ route('admin.tte.signers.activate', $it->id) }}" class="d-inline">
                  @csrf
                  <button class="btn btn-sm btn-outline-success" onclick="return confirm('Aktifkan signer ini?')">
                    Activate
                  </button>
                </form>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-center text-muted py-4">Belum ada signer.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div class="mt-3">
  {{ $items->links() }}
</div>
@endsection