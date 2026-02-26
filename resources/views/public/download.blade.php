@extends('public.layout')

@section('title', 'Cari Sertifikat - E-Sertifikat')
@section('breadcrumb', 'Home / Cari Sertifikat')

@section('content')
  <div class="content-card">
    <div class="border-bottom px-4 py-3 text-center fw-semibold">
      Form untuk mencari data Sertifikat BPMP
    </div>

    <div class="p-4 p-md-5 text-center">
      <h3 class="fw-bold mb-4">Ketik Nama atau NIP atau NUPTK kemudian tekan tombol Cari</h3>

      <form method="POST" action="{{ route('public.download.process') }}"
            class="d-flex justify-content-center gap-2 flex-wrap">
        @csrf

        <input type="text"
               name="q"
               value="{{ old('q') }}"
               class="form-control form-control-lg"
               style="max-width: 520px"
               placeholder="Tulis disini........"
               required>

        <button class="btn btn-warning btn-lg px-4 fw-semibold" type="submit">
          🔍 CARI
        </button>
      </form>

      @if(session('error'))
        <div class="alert alert-danger mt-4 mb-0">{{ session('error') }}</div>
      @endif

      @if(session('success'))
        <div class="alert alert-success mt-4 mb-0">{{ session('success') }}</div>
      @endif
    </div>
  </div>
@endsection
