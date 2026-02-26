<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'E-Sertifikat')</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    .topbar { border-bottom: 1px solid #eee; background: #fff; }
    .brand-left img { height: 52px; }
    .brand-right img { height: 42px; }
    .breadcrumb-wrap { background: #f6f7f9; border-top: 1px solid #eee; border-bottom: 1px solid #eee; }
    .content-card { border: 1px solid #e9ecef; border-radius: 12px; overflow: hidden; background: #fff; }
    .menu a { font-weight: 600; letter-spacing: .5px; }
    .menu a:hover { text-decoration: underline; }
  </style>
</head>
<body class="bg-white">

  {{-- HEADER --}}
 <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
  <div class="container">

    {{-- ✅ SATU LOGO SAJA --}}
    <a class="navbar-brand d-flex align-items-center gap-3" href="/">
      <img src="{{ asset('images/logo.png') }}" 
           alt="Kemendikdasmen"
           style="height:60px;">
      <div>
        <div class="fw-bold">SERTIFIKAT DIGITAL</div>
        <div class="small text-muted">
          Balai Penjaminan Mutu Pendidikan (BPMP)
        </div>
      </div>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav gap-3">
        <li class="nav-item">
          <a class="nav-link fw-semibold" href="/">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link fw-semibold" href="{{ route('public.verify.form') }}">Verification</a>
        </li>
        <li class="nav-item">
          <a class="nav-link fw-semibold" href="#">Cari Sertifikat</a>
        </li>
        <li class="nav-item">
          <a class="nav-link fw-semibold" href="{{ route('login') }}">Login</a>
        </li>
      </ul>
    </div>

  </div>
</nav>
        
      </div>
    </div>
  </div>

  {{-- BREADCRUMB BAR --}}
  <div class="breadcrumb-wrap">
    <div class="container py-3">
      <div class="d-inline-flex align-items-center gap-2 bg-danger text-white px-3 py-2 rounded-3">
        <span class="fw-semibold">@yield('breadcrumb', 'Home')</span>
      </div>
    </div>
  </div>

  {{-- CONTENT --}}
  <div class="container py-5">
    @yield('content')
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
