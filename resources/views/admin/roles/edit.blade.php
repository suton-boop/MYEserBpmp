@extends('layouts.app')
@section('title','Kelola Permission Role')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Kelola Permission Role</h4>
        <div class="text-muted small">
            Role: <span class="fw-semibold">{{ $role->name }}</span>
        </div>
    </div>

    <a href="{{ route('admin.system.roles.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
        <i class="fa-solid fa-arrow-left me-1"></i> Kembali
    </a>
</div>

{{-- ERROR VALIDATION --}}
@if ($errors->any())
<div class="alert alert-danger">
    <div class="fw-semibold mb-1">Terjadi kesalahan:</div>
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form action="{{ route('admin.system.roles.update', $role->id) }}" method="POST">
    @csrf
    @method('PATCH')

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Daftar Permission</span>

            <span class="badge bg-primary">
                Total: {{ $permissions->count() }}
            </span>
        </div>

        <div class="card-body">
            <div class="row g-3">

                {{-- CHECKBOX PERMISSION --}}
                @foreach($permissions as $p)
                <div class="col-md-4 col-lg-3">
                    <div class="form-check border rounded-3 p-2 bg-light">
                        <input class="form-check-input"
                               type="checkbox"
                               name="permissions[]"
                               value="{{ $p->id }}"
                               id="perm{{ $p->id }}"
                               {{ in_array($p->id, $rolePerms) ? 'checked' : '' }}>

                        <label class="form-check-label fw-semibold" for="perm{{ $p->id }}">
                            {{ $p->name }}
                        </label>
                    </div>
                </div>
                @endforeach

            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Centang permission yang ingin diberikan ke role ini.
                </div>

                <button type="submit" class="btn btn-success rounded-3">
                    <i class="fa-solid fa-save me-1"></i> Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
</form>
@endsection
