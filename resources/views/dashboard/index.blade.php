@extends('layouts.app')
@section('title','Dashboard')

@section('content')
<div class="row g-3">
  <div class="col-md-4">
    <div class="card border-0 shadow-sm rounded-4">
      <div class="card-body">
        <div class="text-muted">Role</div>
        <div class="h5 fw-bold mb-0">{{ auth()->user()->role->name ?? '-' }}</div>
      </div>
    </div>
  </div>

  <div class="col-md-8">
    <div class="card border-0 shadow-sm rounded-4">
      <div class="card-body">
        <div class="h5 fw-bold">Selamat datang 👋</div>
        <div class="text-muted">Pilih menu di sidebar untuk mulai mengelola e-sertifikat.</div>
      </div>
    </div>
  </div>
</div>
@endsection
