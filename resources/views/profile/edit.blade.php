@extends('layouts.app')
@section('title', 'Profil Pengguna')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="page-title mb-1">Profil Pengguna</h4>
        <div class="page-subtitle">Perbarui informasi profil dan kata sandi Anda di sini.</div>
    </div>
</div>

<div class="row">
    <!-- Profil Info -->
    <div class="col-md-6">
        <div class="card card-soft mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <h5 class="card-title fw-bold mb-0">Informasi Akun</h5>
                    <button type="button" id="profile-edit-btn" class="btn btn-sm btn-outline-primary">
                        <i class="fa-solid fa-edit me-1"></i> Edit Profil
                    </button>
                </div>
                <p class="text-muted small mb-4">Perbarui nama lengkap dan alamat email Anda.</p>

                @if (session('status') === 'profile-updated')
                    <div class="alert alert-success alert-dismissible fade show text-sm" role="alert">
                        <strong>Berhasil!</strong> Profil Anda telah diperbarui.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form id="profile-form" method="post" action="{{ route('admin.profile.update') }}">
                    @csrf
                    @method('patch')

                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Lengkap</label>
                        <input id="name" name="name" type="text" class="form-control" value="{{ old('name', $user->name) }}" required autocomplete="name" readonly />
                        @error('name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" name="email" type="email" class="form-control" value="{{ old('email', $user->email) }}" required autocomplete="username" readonly />
                        @error('email')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="text-end">
                        <button type="submit" id="profile-save-btn" class="btn btn-primary" style="display: none;">
                            <i class="fa-solid fa-save me-1"></i> Simpan Profil
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Ganti Sandi -->
    <div class="col-md-6">
        <div class="card card-soft mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <h5 class="card-title fw-bold mb-0">Perbarui Kata Sandi</h5>
                    <button type="button" id="password-edit-btn" class="btn btn-sm btn-outline-warning text-dark">
                        <i class="fa-solid fa-key me-1"></i> Ubah Sandi
                    </button>
                </div>
                <p class="text-muted small mb-4">Pastikan Anda menggunakan kata sandi yang kuat dan aman.</p>

                @if (session('status') === 'password-updated')
                    <div class="alert alert-success alert-dismissible fade show text-sm" role="alert">
                        <strong>Berhasil!</strong> Kata sandi Anda telah diperbarui.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form id="password-form" method="post" action="{{ route('password.update') }}">
                    @csrf
                    @method('put')

                    <div class="mb-3">
                        <label for="update_password_current_password" class="form-label">Kata Sandi Saat Ini</label>
                        <input id="update_password_current_password" name="current_password" type="password" class="form-control" autocomplete="current-password" readonly />
                        @error('current_password', 'updatePassword')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="update_password_password" class="form-label">Kata Sandi Baru</label>
                        <input id="update_password_password" name="password" type="password" class="form-control" autocomplete="new-password" readonly />
                        @error('password', 'updatePassword')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="update_password_password_confirmation" class="form-label">Konfirmasi Kata Sandi Baru</label>
                        <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="form-control" autocomplete="new-password" readonly />
                        @error('password_confirmation', 'updatePassword')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="text-end">
                        <button type="submit" id="password-save-btn" class="btn btn-warning text-dark" style="display: none;">
                            <i class="fa-solid fa-save me-1"></i> Simpan Sandi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const profileInputs = document.querySelectorAll('#profile-form input');
        const profileSaveBtn = document.querySelector('#profile-save-btn');
        const profileEditBtn = document.querySelector('#profile-edit-btn');

        const passwordInputs = document.querySelectorAll('#password-form input');
        const passwordSaveBtn = document.querySelector('#password-save-btn');
        const passwordEditBtn = document.querySelector('#password-edit-btn');

        // Toggle function
        function enableEdit(inputs, saveBtn, editBtn) {
            editBtn.addEventListener('click', function() {
                inputs.forEach(input => input.removeAttribute('readonly'));
                saveBtn.style.display = 'inline-block';
                editBtn.style.display = 'none';
                inputs[0].focus();
            });
        }

        enableEdit(profileInputs, profileSaveBtn, profileEditBtn);
        enableEdit(passwordInputs, passwordSaveBtn, passwordEditBtn);

        // Auto activate if there are errors (optional but good for UX)
        @if ($errors->any())
            profileEditBtn.click();
        @endif
        @if ($errors->updatePassword->any())
            passwordEditBtn.click();
        @endif
    });
</script>
@endsection
