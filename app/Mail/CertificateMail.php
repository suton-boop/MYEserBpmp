<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use App\Models\Certificate;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CertificateMail extends Mailable
{
    use Queueable, SerializesModels;

    public $certificate;
    public $participant;
    public $event;

    /**
     * Create a new message instance.
     */
    public function __construct(Certificate $certificate)
    {
        $this->certificate = $certificate;
        $this->participant = $certificate->participant;
        $this->event = $certificate->event;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Sertifikat Digital: ' . $this->event->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.certificate',
            with: [
                'verifyUrl' => route('public.verify.show', $this->certificate->verify_token),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $path = $this->certificate->signed_pdf_path ?: $this->certificate->pdf_path;
        
        if ($path && Storage::disk('public')->exists($path)) {
            return [
                \Illuminate\Mail\Mailables\Attachment::fromPath(Storage::disk('public')->path($path))
                    ->as('Sertifikat-' . Str::slug($this->participant->name) . '.pdf')
                    ->withMime('application/pdf'),
            ];
        }

        return [];
    }
}
