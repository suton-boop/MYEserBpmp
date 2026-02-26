<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>E-Sertifikat</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <nav class="navbar navbar-expand-lg bg-white border-bottom">
    <div class="container">
      <a class="navbar-brand fw-bold" href="/">E-Sertifikat</a>
      <div class="ms-auto">
        <a class="btn btn-outline-primary" href="/login">Login</a>
      </div>
    </div>
  </nav>

  <header class="py-5">
    <div class="container">
      <div class="p-5 rounded-4 bg-white border">
        <h1 class="fw-bold mb-2">Generate Sertifikat Otomatis</h1>
        <p class="text-muted mb-4">Buat event, upload peserta, generate PDF + QR, kirim email, dan verifikasi publik.</p>
        <a class="btn btn-primary" href="/login">Masuk Dashboard</a>
        <a class="btn btn-link" href="/verify">Verifikasi Sertifikat</a>
      </div>
    </div>
  </header>

  <footer class="py-4">
    <div class="container text-muted small">
      © {{ date('Y') }} E-Sertifikat
    </div>
  </footer>
</body>
</html>
