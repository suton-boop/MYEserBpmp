<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'E-Sertifikat BPMP Kaltim')</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}?v={{ time() }}" type="image/x-icon">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root{
            --nav-blue:#0d4f8b;
            --nav-blue-2:#2f7dbf;
            --text-dark:#1f2937;
            --border:#e5e7eb;
            --muted:#6b7280;
        }

        body{
            background:#fff;
            color:var(--text-dark);
            font-family: Arial, Helvetica, sans-serif;
        }

        .container-main{
            max-width: 1280px;
        }

        /* HEADER UTAMA */
        .header-gov{
            background: var(--nav-blue);
            color:#fff;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }

        .header-gov .navbar{
            min-height: 86px;
            padding-top: .75rem;
            padding-bottom: .75rem;
        }

        .brand-logo{
            display:flex;
            align-items:center;
            text-decoration:none;
            gap:14px;
        }

        .brand-logo img{
            height:82px;
            width:auto;
            object-fit:contain;
            display:block;
        }

        .navbar-gov .nav-link{
            color:#fff !important;
            font-weight:500;
            font-size: 1rem;
            padding:.5rem .8rem !important;
            white-space:nowrap;
        }

        .navbar-gov .nav-link:hover,
        .navbar-gov .nav-link:focus{
            color:#dbeafe !important;
        }

        .navbar-gov .dropdown-toggle::after{
            margin-left:.4rem;
            vertical-align:.15rem;
        }

        .navbar-gov .navbar-toggler{
            border-color: rgba(255,255,255,.35);
        }

        .navbar-gov .navbar-toggler:focus{
            box-shadow:none;
        }

        .navbar-gov .navbar-toggler-icon{
            filter: brightness(0) invert(1);
        }

        /* INFO BAR */
        .top-info-bar{
            background: var(--nav-blue-2);
            color:#eaf4ff;
            font-size: 15px;
            line-height:1.4;
        }

        .top-info-bar a{
            color:#fff;
            font-weight:600;
            text-decoration:none;
        }

        .top-info-bar a:hover{
            text-decoration:underline;
        }

        /* BREADCRUMB */
        .breadcrumb-wrap{
            background:#f5f7fa;
            border-top:1px solid var(--border);
            border-bottom:1px solid var(--border);
        }

        .breadcrumb-pill{
            display:inline-flex;
            align-items:center;
            gap:.5rem;
            background:#dc3545;
            color:#fff;
            padding:8px 16px;
            border-radius:12px;
            font-weight:600;
            font-size:.95rem;
            line-height:1;
        }

        /* CONTENT */
        .page-content{
            min-height:60vh;
            padding-top:40px;
            padding-bottom:40px;
        }

        /* FOOTER */
        .footer-public{
            background-color:#1778f2;
        }

        .footer-link{
            color:#fff;
            text-decoration:none;
        }

        .footer-link:hover{
            color:#dbeafe;
            text-decoration:underline;
        }

        /* RESPONSIVE */
        @media (max-width: 1199.98px){
            .brand-logo img{
                height:72px;
            }

            .navbar-gov .nav-link{
                font-size:.95rem;
                padding:.5rem .55rem !important;
            }
        }

        @media (max-width: 991.98px){
            .header-gov .navbar{
                min-height:auto;
            }

            .brand-logo img{
                height:64px;
            }

            .navbar-gov .navbar-collapse{
                margin-top:12px;
                padding-top:10px;
                border-top:1px solid rgba(255,255,255,.12);
            }

            .navbar-gov .nav-link{
                padding:.7rem 0 !important;
            }

            .top-info-bar{
                font-size:14px;
                text-align:center;
            }
        }

        @media (max-width: 575.98px){
            .brand-logo img{
                height:40px;
            }

            .breadcrumb-pill{
                font-size:.9rem;
                padding:7px 14px;
            }

            .page-content{
                padding-top:28px;
                padding-bottom:28px;
            }
        }
    </style>
</head>
<body>

    {{-- HEADER versi 2--}}
    <header class="header-gov">
        <div class="container container-main">
            <nav class="navbar navbar-expand-lg navbar-dark navbar-gov">
                <a class="brand-logo navbar-brand m-0" href="/">
                    <img src="{{ asset('public/images/Asset 2@4x.png') }}" alt="Kemendikdasmen">
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
          <a class="nav-link fw-semibold" href="{{ route('public.search') }}">Cari Sertifikat</a>
        </li>
         <li class="nav-item">
          <a class="nav-link fw-semibold" href="https://bpmpkaltim.kemendikdasmen.go.id/">BPMPKALTIM</a>
        </li>
        <li class="nav-item">
          <a class="nav-link fw-semibold" href="{{ route('login') }}">Login</a>
        </li>
      </ul>
    </div>

  </div>
            </nav>
        </div>
    </header>

    {{-- BAR INFORMASI BAWAH HEADER --}}
    <div class="top-info-bar">
        <div class="container container-main py-2 d-flex flex-column flex-lg-row justify-content-between align-items-center gap-2">
            <div>
               
            </div>
            <div>
                
            </div>
        </div>
    </div>

    {{-- BREADCRUMB --}}
    <div class="breadcrumb-wrap">
        <div class="container container-main py-3">
            <span class="breadcrumb-pill">
                <i class="fa-solid fa-house"></i>
                @yield('breadcrumb', 'Beranda')
            </span>
        </div>
    </div>

    {{-- CONTENT --}}
    <main class="container container-main page-content">
        @yield('content')
    </main>

    {{-- FOOTER --}}
    <footer class="footer-public text-white py-4 mt-5 position-relative overflow-hidden">
        <div class="position-absolute w-100 h-100 opacity-10" style="top: 0; left: 0; background-image: radial-gradient(circle at 2px 2px, rgba(255,255,255,0.8) 1px, transparent 0); background-size: 30px 30px;"></div>

        <div class="container container-main position-relative z-1">
            <div class="row justify-content-center">
                <div class="col-12 col-md-auto">
                    <div class="d-flex flex-column gap-3 mx-auto mt-2" style="max-width:max-content;">

                        <a href="https://bpmpkaltim.kemendikdasmen.go.id" target="_blank" class="footer-link d-flex align-items-center gap-3">
                            <div class="text-dark bg-transparent d-flex align-items-center justify-content-center" style="width:40px; height:40px; font-size:32px;">
                                <i class="fa-solid fa-globe"></i>
                            </div>
                            <div class="text-start">
                                <div class="fw-normal" style="font-size:15px; letter-spacing:.3px; color:#f8f9fa;">
                                    https://bpmpkaltim.kemendikdasmen.go.id
                                </div>
                            </div>
                        </a>

                        <a href="https://youtube.com/" target="_blank" class="footer-link d-flex align-items-center gap-3">
                            <div class="bg-danger text-white rounded d-flex align-items-center justify-content-center" style="width:40px; height:28px; border-radius:8px !important; font-size:18px;">
                                <i class="fa-brands fa-youtube"></i>
                            </div>
                            <div class="text-start">
                                <div class="fw-normal" style="font-size:16px; letter-spacing:.3px; color:#f8f9fa;">
                                    BPMP Provinsi Kalimantan Timur
                                </div>
                            </div>
                        </a>

                        <a href="https://instagram.com/bpmpprovkaltim" target="_blank" class="footer-link d-flex align-items-center gap-3">
                            <div class="text-white rounded d-flex align-items-center justify-content-center" style="width:40px; height:40px; border-radius:10px !important; font-size:26px; background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);">
                                <i class="fa-brands fa-instagram"></i>
                            </div>
                            <div class="text-start">
                                <div class="fw-normal" style="font-size:16px; letter-spacing:.3px; color:#f8f9fa;">
                                    bpmpprovkaltim
                                </div>
                            </div>
                        </a>

                        <a href="https://facebook.com/" target="_blank" class="footer-link d-flex align-items-center gap-3 px-1">
                            <div class="text-white bg-transparent d-flex align-items-center justify-content-center" style="width:32px; height:32px; font-size:32px;">
                                <i class="fa-brands fa-facebook-f"></i>
                            </div>
                            <div class="text-start ms-1">
                                <div class="fw-normal" style="font-size:16px; letter-spacing:.3px; color:#f8f9fa;">
                                    BPMP Provinsi Kalimantan Timur
                                </div>
                            </div>
                        </a>

                        <div class="text-white d-flex align-items-center gap-3 mt-1">
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width:40px; height:40px; font-size:24px;">
                                <i class="fa-brands fa-whatsapp"></i>
                            </div>
                            <div class="text-start">
                                <div class="fw-normal" style="font-size:15px; color:#f8f9fa; line-height:1.2;">
                                    Unit Layanan Terpadu
                                </div>
                                <div class="fw-normal" style="font-size:16px; color:#f8f9fa; line-height:1.2;">
                                    0821 4878 8787
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="border-top border-white border-opacity-25 mt-4 pt-3 text-center" style="font-size:13px; color:#e9ecef;">
                &copy; {{ date('Y') }} Balai Penjaminan Mutu Pendidikan (BPMP) Provinsi Kalimantan Timur. All rights reserved.
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>