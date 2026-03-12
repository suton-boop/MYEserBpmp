<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login - E-Sertifikat</title>

@vite(['resources/css/app.css','resources/js/app.js'])
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>

*, ::before, ::after {
box-sizing: border-box;
}

body{
background: linear-gradient(135deg,#eef4ff 0%,#f8fafc 60%,#e8f1ff 100%);
font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial;
}

.login-wrapper{
min-height:100vh;
display:flex;
align-items:center;
justify-content:center;
padding:40px 20px;
}

.login-card{
background:#ffffff;
border-radius:28px;
padding:40px 45px;
max-width:480px;
width:100%;
box-shadow:
0 30px 70px rgba(0,0,0,0.08),
0 10px 25px rgba(0,0,0,0.05);
}

.logo-top{
height:90px;
width: auto;
max-width: 100%;
object-fit:contain;
margin-bottom:20px;
display: block;
margin-left: auto;
margin-right: auto;
}

.title-login{
font-size:40px;
font-weight:800;
color:#1e293b;
text-align: center;
}

.subtitle-login{
color:#64748b;
font-size:18px;
margin-top:6px;
margin-bottom:35px;
text-align: center;
}

.label-login{
font-weight:600;
color:#475569;
margin-bottom:8px;
}

.input-box{
position:relative;
}

.input-box i.left-icon{
position:absolute;
left:16px;
top:50%;
transform:translateY(-50%);
color:#94a3b8;
}

.input-box .password-toggle{
position:absolute;
right:16px;
top:50%;
transform:translateY(-50%);
color:#94a3b8;
cursor:pointer;
border:none;
background:none;
padding:0;
transition:0.2s;
}

.input-box .password-toggle:hover{
color:#3b82f6;
}

.input-field{
width:100%;
padding:14px 16px 14px 45px;
border-radius:18px;
border:1px solid #cbd5e1;
font-size:16px;
transition:0.25s;
}

.input-field:focus{
outline:none;
border-color:#3b82f6;
box-shadow:0 0 0 4px rgba(59,130,246,.15);
}

.btn-login{
margin-top:10px;
width:100%;
padding:15px;
border:none;
border-radius:20px;
font-weight:700;
font-size:17px;
color:white;
background:linear-gradient(135deg,#0d6efd,#00b4db);
box-shadow:0 10px 20px rgba(13,110,253,.3);
transition:.2s;
}

.btn-login:hover{
transform:translateY(-2px);
box-shadow:0 15px 30px rgba(13,110,253,.35);
}

.footer-login{
text-align:center;
margin-top:40px;
color:#64748b;
font-size:14px;
}

.footer-login img{
height:55px;
margin-bottom:10px;
}

</style>
</head>

<body>

<div class="login-wrapper">

<div class="login-card">

<div class="text-center flex flex-col items-center">

<img src="{{ asset('public/images/logo-kemendikdasmen.png') }}"
class="logo-top mx-auto mb-4">

<div class="title-login">
Selamat Datang
</div>

<div class="subtitle-login">
Silakan login menggunakan email dan kata sandi Anda untuk mengakses dashboard manajemen.
</div>

</div>

@if($errors->any())
<div class="mb-4 text-red-600 text-sm">
<ul>
@foreach ($errors->all() as $error)
<li>{{ $error }}</li>
@endforeach
</ul>
</div>
@endif

<form method="POST" action="{{ route('login') }}">
@csrf

<div class="mb-4">
<label class="label-login">Email Anda</label>

<div class="input-box">
<i class="fa-solid fa-envelope left-icon"></i>

<input
type="email"
name="email"
value="{{ old('email') }}"
class="input-field"
placeholder="contoh@kemdikbud.go.id"
required
>
</div>
</div>

<div class="mb-4">

<div class="flex justify-between items-center mb-1">
<label class="label-login">Kata Sandi</label>

@if (Route::has('password.request'))
<a href="{{ route('password.request') }}" class="text-blue-600 text-sm font-semibold">
Lupa sandi?
</a>
@endif

</div>

<div class="input-box">
<i class="fa-solid fa-lock left-icon"></i>

<input
type="password"
name="password"
id="password"
class="input-field"
placeholder="••••••••"
required
>

<button type="button" class="password-toggle" id="togglePassword">
<i class="fa-solid fa-eye" id="eyeIcon"></i>
</button>
</div>

</div>

<div class="mb-5 flex items-center">
<input type="checkbox" name="remember" class="mr-2">
<span class="text-sm text-slate-600">Ingat Saya</span>
</div>

<button type="submit" class="btn-login">
Masuk Dasbor →
</button>

</form>

<div class="footer-login flex flex-col items-center text-center">

<img src="{{ asset('public/images/ramah bermutu.png') }}"
class="h-14 mb-3 mx-auto">

<div>
© {{ date('Y') }} Kementerian Pendidikan Dasar dan Menengah<br>
Balai Penjaminan Mutu Pendidikan Provinsi Kalimantan Timur
</div>

</div>

</div>

</div>

</div>

<script>
const togglePassword = document.querySelector('#togglePassword');
const password = document.querySelector('#password');
const eyeIcon = document.querySelector('#eyeIcon');

if (togglePassword && password && eyeIcon) {
    togglePassword.addEventListener('click', function (e) {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        eyeIcon.classList.toggle('fa-eye');
        eyeIcon.classList.toggle('fa-eye-slash');
    });
}
</script>
</body>
</html>