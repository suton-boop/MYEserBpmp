@extends('layouts.app')
@section('title','TTE Dashboard')

@section('content')
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
      <h4 class="mb-0">TTE Dashboard</h4>
      <div class="text-muted">Ringkasan modul TTE.</div>
    </div>

    <div class="d-flex gap-2">
      <a class="btn btn-outline-primary" href="{{ route('admin.tte.signing.index') }}">
        <i class="fa-solid fa-signature me-1"></i> Signing Queue
      </a>
      <a class="btn btn-outline-secondary" href="{{ route('admin.tte.signers.index') }}">
        <i class="fa-solid fa-key me-1"></i> Signer Certificates
      </a>
    </div>
  </div>

  <div class="alert alert-info">
    Jika ingin statistik (signedToday, pendingApproved, activeSigners), pastikan controller mengirimkan variabelnya ke view.
  </div>
@endsection