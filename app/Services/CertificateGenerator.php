<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Event;
use App\Models\Participant;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CertificateGenerator
{
    /**
     * Generate PDF dan simpan ke storage.
     * Return: path file pdf (relative to disk public)
     */
    public function generate(Event $event, Participant $participant, Certificate $certificate): string
    {
        $template = $event->certificateTemplate;

        // Background template (optional) -> dari storage public
        $bgUrl = null;
        if ($template && $template->file_path) {
            // file_path contoh: certificate-templates/xxxx.png
            $bgUrl = Storage::disk('public')->url($template->file_path);
        }

        // QR pakai Google Chart (tidak perlu imagick)
        $verifyUrl = route('public.verify', ['code' => $certificate->code]);
        $qrUrl = 'https://chart.googleapis.com/chart?chs=220x220&cht=qr&chl=' . urlencode($verifyUrl);

        $settings = $template?->settings ?? []; // json array kalau ada

        $pdf = Pdf::loadView('certificates.pdf', [
            'event'       => $event,
            'participant' => $participant,
            'certificate' => $certificate,
            'bgUrl'       => $bgUrl,
            'qrUrl'       => $qrUrl,
            'settings'    => $settings,
            'verifyUrl'   => $verifyUrl,
        ])->setPaper('a4', 'landscape');

        // Simpan file
        $safeEvent = Str::slug($event->name);
        $filename  = $certificate->code . '.pdf';
        $path      = "certificates/{$safeEvent}/{$filename}";

        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }
    public function view(Certificate $certificate)
    {
    if (!$certificate->pdf_path) {
        return back()->with('error', 'PDF belum tersedia.');
    }

    if (!Storage::disk('public')->exists($certificate->pdf_path)) {
        return back()->with('error', 'File PDF tidak ditemukan di storage/public.');
    }

    $fullPath = Storage::disk('public')->path($certificate->pdf_path);

    return response()->file($fullPath, [
        'Content-Type'        => 'application/pdf',
        'Content-Disposition' => 'inline; filename="sertifikat-'.$certificate->id.'.pdf"',
    ]);
    }

}
