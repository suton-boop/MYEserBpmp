@extends('layouts.app')
@section('title', 'Tambah Signer')

@section('content')
<div class="mb-3">
  <h4 class="mb-0">Tambah Signer Certificate</h4>
  <div class="text-muted">Masukkan public/private key PEM. Private key akan disimpan terenkripsi (Laravel Crypt).</div>
</div>

@if($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">
      @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
    </ul>
  </div>
@endif

<div class="card rounded-3">
  <div class="card-body">
    <form method="POST" action="{{ route('admin.tte.signers.store') }}">
      @csrf

      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Code</label>
          <input class="form-control" name="code" value="{{ old('code') }}" placeholder="SIGNER-001" required>
        </div>
        <div class="col-md-8">
          <label class="form-label">Name</label>
          <input class="form-control" name="name" value="{{ old('name') }}" placeholder="Signer Utama" required>
        </div>

        <div class="col-md-6">
          <label class="form-label">Valid From</label>
          <input class="form-control" type="date" name="valid_from" value="{{ old('valid_from') }}">
        </div>
        <div class="col-md-6">
          <label class="form-label">Valid To</label>
          <input class="form-control" type="date" name="valid_to" value="{{ old('valid_to') }}">
        </div>

        <div class="col-12">
          <label class="form-label">Public Key PEM</label>
          <textarea class="form-control" name="public_key_pem" rows="8" required>{{ old('public_key_pem') }}</textarea>
        </div>
        <div class="col-12">
          <label class="form-label">Private Key PEM</label>
          <textarea class="form-control" name="private_key_pem" rows="10" required>{{ old('private_key_pem') }}</textarea>
          <div class="form-text text-danger">
            Jangan simpan private key di git. Input hanya lewat admin yang trusted.
          </div>
        </div>

        <div class="col-12 d-flex gap-2">
          <a class="btn btn-outline-secondary" href="{{ route('admin.tte.signers.index') }}">Batal</a>
          <button class="btn btn-primary">Simpan</button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection