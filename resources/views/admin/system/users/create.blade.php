@extends('layouts.app')
@section('title','Tambah User')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h4 class="mb-0">Tambah User</h4>
    <div class="text-muted">Buat user baru dan pilih role.</div>
  </div>
  <a href="{{ route('admin.system.users.index') }}" class="btn btn-outline-secondary rounded-3">Kembali</a>
</div>

@if ($errors->any())
  <div class="alert alert-danger">
    <div class="fw-semibold mb-1">Periksa input:</div>
    <ul class="mb-0">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

<form method="POST" action="{{ route('admin.system.users.store') }}" class="card border-0 shadow-sm rounded-4">
  @csrf
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Nama</label>
        <input name="name" class="form-control" value="{{ old('name') }}" required>
      </div>

      <div class="col-md-6">
        <label class="form-label">Email</label>
        <input name="email" type="email" class="form-control" value="{{ old('email') }}" required>
      </div>

      <div class="col-md-6">
        <label class="form-label">Role</label>
        <select name="role_id" class="form-select" required>
          <option value="">-- Pilih Role --</option>
          @foreach($roles as $r)
            <option value="{{ $r->id }}" @selected(old('role_id')==$r->id)>{{ $r->name }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-md-6"></div>

      <div class="col-md-6">
        <label class="form-label">Password</label>
        <input name="password" type="password" class="form-control" required>
      </div>

      <div class="col-md-6">
        <label class="form-label">Konfirmasi Password</label>
        <input name="password_confirmation" type="password" class="form-control" required>
      </div>
    </div>
  </div>

  <div class="card-footer bg-white d-flex justify-content-end">
    <button class="btn btn-primary rounded-3">Simpan</button>
  </div>
</form>
@endsection
