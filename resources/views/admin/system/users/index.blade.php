@extends('layouts.app')
@section('title','Kelola User')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
  <div>
    <h4 class="fw-bold mb-1">Kelola User</h4>
    <div class="text-muted small">Kelola akses akun dan hak istimewa pengguna sistem.</div>
  </div>

  <div class="d-flex flex-wrap gap-2">
    @php $roleName = strtolower(auth()->user()->role->name ?? ''); @endphp
    @if($roleName === 'superadmin' || $roleName === 'super admin')
        <a href="{{ route('admin.system.users.import.form') }}" class="btn btn-soft-primary rounded-3 px-3">
            <i class="fa-solid fa-file-import me-1"></i> Import User
        </a>
    @endif
    <a href="{{ route('admin.system.users.create') }}" class="btn btn-primary rounded-3 px-3 shadow-sm">
      <i class="fa-solid fa-plus me-1"></i> Tambah User
    </a>
  </div>
</div>

<style>
    .btn-soft-primary {
        background-color: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
        border: 1px solid rgba(13, 110, 253, 0.2);
    }
    .btn-soft-primary:hover {
        background-color: #0d6efd;
        color: #fff;
    }
</style>

@if(session('success'))
  <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4 rounded-3">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

@if(session('warning'))
  <div class="alert alert-warning alert-dismissible fade show shadow-sm border-0 mb-4 rounded-3">
    {{ session('warning') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

@if(session('error'))
  <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4 rounded-3">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
  <div class="card-header bg-white border-0 py-3">
    <span class="fw-bold"><i class="fa-solid fa-users-gear me-2 text-primary"></i>Daftar User</span>
  </div>

  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light text-muted small">
        <tr>
          <th width="5%" class="ps-4">#</th>
          <th>Nama</th>
          <th>Email</th>
          <th>Role</th>
          <th width="15%" class="text-end pe-4">Aksi</th>
        </tr>
      </thead>

      <tbody>
        @forelse($users as $u)
          <tr>
            <td class="ps-4 text-muted small">{{ $loop->iteration }}</td>
            <td>
                <div class="fw-bold text-dark">{{ $u->name }}</div>
                @if($u->id === auth()->id())
                   <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25" style="font-size: 0.6rem;">ANDA</span>
                @endif
            </td>
            <td class="text-muted">{{ $u->email }}</td>
            <td>
              @php
                $roleBadge = match($u->role?->name) {
                    'superadmin' => 'bg-danger',
                    'admin' => 'bg-primary',
                    default => 'bg-secondary'
                };
              @endphp
              <span class="badge {{ $roleBadge }} bg-opacity-10 {{ str_replace('bg-', 'text-', $roleBadge) }} border {{ str_replace('bg-', 'border-', $roleBadge) }} border-opacity-25 px-2 py-1" style="font-size: 0.75rem;">
                {{ strtoupper($u->role?->name ?? '-') }}
              </span>
            </td>
            <td class="text-end pe-4">
              <div class="d-flex justify-content-end gap-1">
                  <a href="{{ route('admin.system.users.edit', $u->id) }}" class="btn btn-sm btn-outline-warning border-0 rounded-circle" style="width:32px; height:32px;" title="Edit">
                    <i class="fa-solid fa-pen-to-square"></i>
                  </a>

                  <form action="{{ route('admin.system.users.destroy', $u->id) }}"
                        method="POST"
                        onsubmit="return confirm('Yakin hapus user ini?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger border-0 rounded-circle" style="width:32px; height:32px;" title="Hapus">
                      <i class="fa-solid fa-trash-can"></i>
                    </button>
                  </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="text-center text-muted py-5">
                <i class="fa-solid fa-user-slash fs-2 d-block mb-3 opacity-25"></i>
                Belum ada data user.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
