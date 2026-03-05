@extends('layouts.app')
@section('title','Kelola User')

@section('content')
<h4 class="mb-3">Kelola User</h4>

@if(session('success'))
  <div class="alert alert-success alert-dismissible fade show">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

@if(session('error'))
  <div class="alert alert-danger alert-dismissible fade show">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

<div class="card border-0 shadow-sm rounded-4">
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <span class="fw-semibold">Daftar User</span>
    <a href="{{ route('admin.system.users.create') }}" class="btn btn-primary btn-sm rounded-3">
      + Tambah User
    </a>
  </div>

  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th width="5%">#</th>
          <th>Nama</th>
          <th>Email</th>
          <th>Role</th>
          <th width="18%">Aksi</th>
        </tr>
      </thead>

      <tbody>
        @forelse($users as $u)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td class="fw-semibold">{{ $u->name }}</td>
            <td>{{ $u->email }}</td>
            <td>
              <span class="badge bg-secondary">{{ $u->role?->name ?? '-' }}</span>
            </td>
            <td>
              <a href="{{ route('admin.system.users.edit', $u->id) }}" class="btn btn-warning btn-sm rounded-3">
                Edit
              </a>

              <form action="{{ route('admin.system.users.destroy', $u->id) }}"
                    method="POST"
                    class="d-inline"
                    onsubmit="return confirm('Yakin hapus user ini?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger btn-sm rounded-3">
                  Hapus
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="text-center text-muted py-4">Belum ada user</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
