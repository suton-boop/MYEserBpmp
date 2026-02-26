@extends('layouts.app')
@section('title','Edit User')
@php $isSuper = $user->role?->name === 'superadmin'; @endphp
...
<select name="role_id" class="form-select" required @disabled($isSuper)>
@if($isSuper)
  <input type="hidden" name="role_id" value="{{ $user->role_id }}">
  <div class="text-muted small mt-1">Role superadmin dikunci.</div>
@endif

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h4 class="mb-0">Edit User</h4>
    <div class="text-muted">Ubah data user dan role.</div>
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

<form method="POST" action="{{ route('admin.system.users.update', $user->id) }}" class="card border-0 shadow-sm rounded-4">
  @csrf
  @method('PATCH')

  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Nama</label>
        <input name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
      </div>

      <div class="col-md-6">
        <label class="form-label">Email</label>
        <input name="email" type="email" class="form-control" value="{{ old('email', $user->email) }}" required>
      </div>

      <div class="col-md-6">
        <label class="form-label">Role</label>
        <select name="role_id" class="form-select" required>
          @foreach($roles as $r)
            <option value="{{ $r->id }}" @selected(old('role_id', $user->role_id)==$r->id)>
              {{ $r->name }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="col-md-6"></div>

      <div class="col-md-6">
        <label class="form-label">Password Baru (opsional)</label>
        <input name="password" type="password" class="form-control" placeholder="Kosongkan jika tidak diubah">
      </div>

      <div class="col-md-6">
        <label class="form-label">Konfirmasi Password Baru</label>
        <input name="password_confirmation" type="password" class="form-control" placeholder="Kosongkan jika tidak diubah">
      </div>
    </div>

    <div class="text-muted small mt-2">
      Jika password dikosongkan, password tidak berubah.
    </div>
  </div>

  <div class="card-footer bg-white d-flex justify-content-end">
    <button class="btn btn-primary rounded-3">Simpan Perubahan</button>
  </div>
</form>
@endsection
