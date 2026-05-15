@extends('layouts.app')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
  <div>
    <h4 class="mb-0">Template Sertifikat</h4>
    <div class="text-muted">Kelola banyak template, aktifkan/nonaktifkan, dan atur setting.</div>
  </div>

  <a href="{{ route('admin.system.templates.create') }}" class="btn btn-primary rounded-3">
    <i class="fa-solid fa-plus me-1"></i> Tambah Template
  </a>
</div>

@if(session('success'))
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

@if(session('error'))
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

{{-- FILTER CARD --}}
<form method="GET" action="{{ route('admin.system.templates.index') }}" class="card border-0 shadow-sm rounded-4 mb-3">
  <div class="card-body">
    <div class="row g-2 align-items-end">
      <div class="col-lg-6">
        <label class="form-label small text-muted mb-1">Cari</label>
        <input
          type="text"
          name="q"
          class="form-control"
          value="{{ $q ?? '' }}"
          placeholder="Cari nama template atau kode..."
        >
      </div>

      <div class="col-lg-3">
        <label class="form-label small text-muted mb-1">Status</label>
        <select name="status" class="form-select">
          <option value="">-- Semua Status --</option>
          <option value="active" @selected(($status ?? '') === 'active')>Active</option>
          <option value="inactive"  @selected(($status ?? '') === 'inactive')>Inactive</option>
        </select>
      </div>

      <div class="col-lg-3 d-flex gap-2">
        <button class="btn btn-primary w-100" title="Cari" type="submit">
          <i class="fa-solid fa-magnifying-glass me-1"></i> Cari
        </button>
        <a class="btn btn-outline-secondary" href="{{ route('admin.system.templates.index') }}" title="Reset">
          Reset
        </a>
      </div>
    </div>
  </div>
</form>

<div class="card shadow-sm rounded-4 border-0">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th style="width:60px">#</th>
            <th>Nama</th>
            <th style="width:180px">Kode</th>
            <th style="width:160px">Status</th>
            <th style="width:240px" class="text-end">Aksi</th>
          </tr>
        </thead>

        <tbody>
          @forelse($templates as $t)
            <tr>
              <td>{{ $loop->iteration }}</td>

              <td class="fw-semibold">{{ $t->name }}</td>

              <td>
                <span class="badge text-bg-light border">{{ $t->code }}</span>
              </td>

              <td>
                <span class="badge {{ $t->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                  {{ $t->is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>

              <td class="text-end">
                <div class="d-inline-flex gap-2">

                  {{-- VIEW --}}
                  <a href="{{ route('admin.system.templates.show', $t) }}"
                     class="btn btn-info btn-sm rounded-3 text-white"
                     title="Lihat Detail">
                    <i class="fa-solid fa-eye"></i>
                  </a>

                  {{-- EDIT --}}
                  <a href="{{ route('admin.system.templates.edit', $t) }}"
                     class="btn btn-warning btn-sm rounded-3"
                     title="Edit">
                    <i class="fa-solid fa-pen-to-square"></i>
                  </a>

                  {{-- TOGGLE --}}
                  <form method="POST" action="{{ route('admin.system.templates.toggle', $t) }}" class="d-inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit"
                            class="btn btn-outline-primary btn-sm rounded-3"
                            title="Toggle aktif">
                      <i class="fa-solid fa-power-off"></i>
                    </button>
                  </form>

                  {{-- DELETE --}}
                  <form method="POST"
                        action="{{ route('admin.system.templates.destroy', $t) }}"
                        class="d-inline"
                        onsubmit="return confirm('Hapus template ini?');">
                    @csrf
                    @method('DELETE')

                    <button type="submit"
                            class="btn btn-outline-danger btn-sm rounded-3"
                            title="Hapus">
                      <i class="fa-solid fa-trash"></i>
                    </button>
                  </form>

                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center text-muted py-4">
                Belum ada template.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- pagination (opsional) --}}
@if(method_exists($templates, 'links'))
  <div class="mt-3">
    {{ $templates->links() }}
  </div>
@endif
@endsection
