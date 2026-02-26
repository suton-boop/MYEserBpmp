@extends('layouts.app')
@section('title','Kelola Role')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0 fw-bold">Kelola Role</h4>
        <small class="text-muted">Daftar role dan jumlah permission.</small>
    </div>
</div>

{{-- Notifikasi sukses --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3" role="alert">
    <i class="fa-solid fa-circle-check me-1"></i>
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center rounded-top-4">
        <span class="fw-semibold">
            <i class="fa-solid fa-user-shield me-1 text-primary"></i>
            Daftar Role
        </span>

        {{-- Tombol create role jangan dulu, karena routenya belum ada --}}
        {{-- <a href="{{ route('admin.system.roles.create') }}" class="btn btn-primary btn-sm rounded-3">
            <i class="fa-solid fa-plus me-1"></i> Tambah Role
        </a> --}}
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th width="5%">#</th>
                    <th>Role</th>
                    <th width="20%">Jumlah Permission</th>
                    <th width="20%">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($roles as $r)
                <tr>
                    <td>{{ $loop->iteration }}</td>

                    <td class="fw-semibold text-dark">
                        {{ $r->name }}
                    </td>

                    <td>
                        <span class="badge bg-info text-dark px-3 py-2 rounded-pill">
                            {{ $r->permissions->count() }}
                        </span>
                    </td>

                    <td>
                        <a href="{{ route('admin.system.roles.edit', $r->id) }}"
                           class="btn btn-primary btn-sm rounded-3">
                            <i class="fa-solid fa-key me-1"></i> Kelola Permission
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-5">
                        <i class="fa-solid fa-circle-info me-1"></i>
                        Belum ada data role
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
