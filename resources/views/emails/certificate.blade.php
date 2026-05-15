<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sertifikat Digital Anda</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px; }
        .header { text-align: center; margin-bottom: 30px; }
        .content { margin-bottom: 30px; }
        .footer { font-size: 12px; color: #777; text-align: center; border-top: 1px solid #eee; padding-top: 20px; }
        .btn { display: inline-block; padding: 10px 20px; background-color: #0d6efd; color: #fff; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>E-Sertifikat BPMP Kalimantan Timur</h2>
        </div>
        <div class="content">
            <p>Halo <strong>{{ $participant->name }}</strong>,</p>
            <p>Terima kasih telah berpartisipasi dalam kegiatan <strong>{{ $event->name }}</strong>.</p>
            <p>Bersama email ini, kami lampirkan sertifikat digital Anda yang telah ditandatangani secara elektronik (TTE).</p>
            <p>Anda juga dapat memverifikasi keaslian sertifikat ini melalui tautan berikut:</p>
            <p style="text-align: center;">
                <a href="{{ $verifyUrl }}" class="btn">Verifikasi Sertifikat</a>
            </p>
            <p>Atau akses melalui: <a href="{{ $verifyUrl }}">{{ $verifyUrl }}</a></p>
        </div>
        <div class="footer">
            <p>Email ini dikirim secara otomatis oleh Sistem E-Sertifikat BPMP Provinsi Kalimantan Timur.</p>
            <p>&copy; {{ date('Y') }} BPMP Kalimantan Timur</p>
        </div>
    </div>
</body>
</html>
