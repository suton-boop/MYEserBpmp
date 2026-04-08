@extends('layouts.app')
@section('title', 'TTE strict rules')

@section('content')
<div class="row pt-2 pb-4">
    <div class="col-12 mt-2">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="page-title text-dark fw-bold mb-1">Pengaturan Sistem</h4>
                <div class="page-subtitle small">Kelola pengaturan global aplikasi (Super Admin)</div>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <div class="card card-soft mb-4">
            <div class="card-body p-4">
                <form action="{{ route('admin.system.settings.update') }}" method="POST">
                    @csrf

                    <h5 class="fw-bold text-dark mb-4">Pengaturan Tanda Tangan Elektronik</h5>

                    <div class="mb-4">
                        <div class="form-check form-switch fs-5 mb-1">
                            <input class="form-check-input" type="checkbox" role="switch" id="strictTteDate" name="strict_tte_date" value="1" {{ $strictTteDate ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="strictTteDate">
                                Validasi Ketat Tanggal TTE
                            </label>
                        </div>
                        <div class="form-text ms-1 mt-0" style="max-width: 600px;">
                            Jika diaktifkan, sertifikat <strong>tidak akan bisa di-TTE</strong> sebelum tanggal yang tertera pada sertifikat tersebut terlewati. 
                            (Tanggal hari ini harus &ge; Tanggal jadwal sertifikat). Menonaktifkan fitur ini akan memperbolehkan sertifikat di-TTE mendahului/sebelum acara.
                        </div>
                    </div>

                    <hr class="text-muted opacity-25">

                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-primary btn-icon px-4">
                            <i class="fa-solid fa-save"></i> Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
