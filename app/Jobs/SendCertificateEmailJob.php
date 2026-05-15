<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

use App\Models\Certificate;
use App\Mail\CertificateMail;
use Illuminate\Support\Facades\Mail;

class SendCertificateEmailJob implements ShouldQueue
{
    use Queueable;

    public $certificate;

    /**
     * Create a new job instance.
     */
    public function __construct(Certificate $certificate)
    {
        $this->certificate = $certificate;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $participant = $this->certificate->participant;

        if (!$participant || !$participant->email) {
            return;
        }

        // ✅ Validasi format email sebelum dikirim
        if (!filter_var($participant->email, FILTER_VALIDATE_EMAIL)) {
            \Illuminate\Support\Facades\Log::warning("Email tidak valid untuk peserta ID {$participant->id}: {$participant->email}");
            return;
        }

        // Kirim Email
        Mail::to($participant->email)->send(new CertificateMail($this->certificate));

        // Update Status
        $this->certificate->update([
            'status' => Certificate::STATUS_SENT,
            'sent_at' => now(),
        ]);
    }
}
