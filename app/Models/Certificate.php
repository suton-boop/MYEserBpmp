<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    // Status workflow
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_FINAL_GENERATED = 'final_generated';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_SIGNED = 'signed';
    public const STATUS_SENT = 'sent';

    protected $table = 'certificates';

    protected $fillable = [
        'pdf_path',
        'signed_pdf_path',
        'signed_at',
        'verify_token',
        'status',

        'event_id',
        'participant_id',

        // nomor & urutan (dikunci saat approve)
        'certificate_number',
        'year',
        'sequence',

        // file path
        'pdf_path', // final pdf path (atau path final)
        'signed_pdf_path', // jika kamu tambah kolom ini (nullable)

        // security
        'verify_token',

        // workflow
        'status',
        'created_by',
        'submitted_at',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_by',
        'rejected_note',
        'generated_at',
        'signed_at',
        'sent_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'generated_at' => 'datetime',
        'signed_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    // Relations
    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class , 'submitted_by');
    }

    public function digitalSignature()
    {
        return $this->hasOne(DigitalSignature::class);
    }

    // Helper
    public function isStatus(string $status): bool
    {
        return $this->status === $status;
    }
}