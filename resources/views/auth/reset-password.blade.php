<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ubah Kata Sandi Baru - E-Sertifikat</title>
    <link rel="stylesheet" href="{{ asset('build/assets/app-VYkFtGlb.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        .split-bg {
            background: linear-gradient(135deg, #f6f8fd 0%, #f1f5f9 100%);
        }
        .brand-bg {
            background: linear-gradient(135deg, #0d6efd 0%, #00b4db 100%);
            position: relative;
            overflow: hidden;
        }
        .brand-bg::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .glass-panel {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.8);
            border-radius: 20px;
        }
        .input-icon-wrapper i {
            transition: color 0.3s;
        }
        input:focus + .input-icon-wrapper i, input:focus ~ .input-icon-wrapper i {
            color: #0d6efd;
        }
    </style>
</head>
<body class="antialiased min-h-screen flex split-bg text-slate-800">

<div class="flex-1 flex" style="min-height: 100vh;">
    <!-- Left: Branding -->
    <div class="hidden lg:flex flex-col justify-between w-5/12 brand-bg text-white p-12">
        <div class="z-10 relative">
            <h1 class="text-4xl font-extrabold tracking-tight mb-4" style="line-height: 1.2;">E-Sertifikat<br/>BPMP Prov. Kaltim</h1>
            <p class="text-lg opacity-90 max-w-sm font-light">
                Sistem Layanan Dokumen dan Tanda Tangan Elektronik Terpadu untuk kemudahan distribusi sertifikat secara instan dan aman.
            </p>
        </div>
        
        <div class="z-10 relative mt-auto flex flex-col items-start gap-4">
            <div class="glass-panel p-3 inline-block text-slate-800 shadow-xl">
                <img src="{{ asset('images/ramah bermutu.png') }}" alt="Logo Pendidikan Bermutu Untuk Semua" class="h-12 lg:h-16 object-contain">
            </div>
            <div class="text-sm font-medium text-white opacity-90 leading-snug">
                &copy; {{ date('Y') }} Kementerian Pendidikan Dasar dan Menengah<br>
                Balai Penjaminan Mutu Pendidikan Provinsi Kalimantan Timur.
            </div>
        </div>
        
        <!-- Decoration -->
        <div class="absolute -right-20 -bottom-20 opacity-10 z-0">
             <i class="fa-solid fa-award" style="font-size: 35rem;"></i>
        </div>
    </div>

    <!-- Right: Form -->
    <div class="w-full lg:w-7/12 flex items-center justify-center p-8 lg:p-24 relative">
        <div class="max-w-md w-full">
            <div class="mb-8 flex flex-col items-center text-center">
                 <img src="{{ asset('images/logo-kemendikdasmen.png') }}" alt="Logo Kemendikdasmen BPMP Kaltim" class="max-w-[180px] h-auto mb-6 object-contain">
                 <h2 class="text-3xl font-extrabold text-slate-900 mb-2">Simpan Sandi Baru 🔑</h2>
                 <p class="text-slate-500 font-medium text-sm">Silakan buat kata sandi baru untuk akun Anda.</p>
            </div>

            @if(session('status'))
                <div class="mb-5 p-4 rounded-xl bg-green-50 text-green-700 text-sm font-medium border border-green-200 flex items-center">
                    <i class="fa-solid fa-circle-check mr-3 text-lg"></i> {{ session('status') }}
                </div>
            @endif
            
            @if($errors->any())
                 <div class="mb-5 p-4 rounded-xl bg-red-50 text-red-700 text-sm font-medium border border-red-200 flex items-start">
                    <i class="fa-solid fa-triangle-exclamation mr-3 mt-1 text-lg"></i>
                    <ul class="list-disc pl-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password.store') }}" class="space-y-6">
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <!-- Email Address -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">Email Anda</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none input-icon-wrapper">
                            <i class="fa-solid fa-envelope text-slate-400 group-focus-within:text-blue-600 transition-colors"></i>
                        </div>
                        <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus
                            class="block w-full pl-11 pr-4 py-3 border border-slate-300 rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white shadow-sm hover:border-slate-400" placeholder="contoh@kemdikbud.go.id">
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700 mb-2">Kata Sandi Baru</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none input-icon-wrapper">
                            <i class="fa-solid fa-lock text-slate-400 group-focus-within:text-blue-600 transition-colors"></i>
                        </div>
                        <input id="password" type="password" name="password" required autocomplete="new-password"
                            class="block w-full pl-11 pr-4 py-3 border border-slate-300 rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white shadow-sm hover:border-slate-400" placeholder="••••••••">
                    </div>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 mb-2">Konfirmasi Kata Sandi Baru</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none input-icon-wrapper">
                            <i class="fa-solid fa-lock text-slate-400 group-focus-within:text-blue-600 transition-colors"></i>
                        </div>
                        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                            class="block w-full pl-11 pr-4 py-3 border border-slate-300 rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white shadow-sm hover:border-slate-400" placeholder="••••••••">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-md text-sm font-bold text-white transition-all hover:-translate-y-1 hover:shadow-lg" style="background: linear-gradient(135deg, #0d6efd 0%, #00b4db 100%);">
                        Ubah Kata Sandi <i class="fa-solid fa-check ml-2 mt-0.5"></i>
                    </button>
                    
                    <div class="text-center mt-4">
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-800 transition-colors">
                            <i class="fa-solid fa-arrow-left mr-1"></i> Batal & Kembali ke Login
                        </a>
                    </div>
                </div>
            </form>
            
        </div>
    </div>
</div>
</body>
</html>
