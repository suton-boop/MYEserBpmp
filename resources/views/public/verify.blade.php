@extends('public.layout')

@section('title', 'Verification - E-Sertifikat')
@section('breadcrumb', 'Home / Verify Certificate')

@section('content')
  <div class="content-card">
    <div class="border-bottom px-4 py-3 text-center fw-semibold">
      Form untuk verifikasi sertifikat
    </div>

    <div class="p-4 p-md-5 text-center">
      <h3 class="fw-bold mb-4">Masukkan Kode Verifikasi Sertifikat</h3>

      <form onsubmit="event.preventDefault(); window.location.href='{{ url('/verify') }}/'+document.getElementById('code').value;">
        <input id="code"
               type="text"
               class="form-control form-control-lg mx-auto"
               style="max-width: 520px"
               placeholder="Tulis kode disini........"
               required>

        <button class="btn btn-danger btn-lg mt-3 px-4 fw-semibold" type="submit">
          VERIFIKASI
        </button>
      </form>
    </div>
  </div>
@endsection
