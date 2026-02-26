<?php

namespace App\Domain\Certificates\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    use HasUuids;

    protected $fillable = [
        'certificate_no','title','owner_name','owner_identifier',
        'pdf_path','pdf_checksum','status',
        'approval_level_required','approval_level_current',
        'generated_at','signed_at','archived_at','created_by',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
        'signed_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    public function signatures(): HasMany
    {
        return $this->hasMany(DigitalSignature::class);
    }

    public function approvalLogs(): HasMany
    {
        return $this->hasMany(ApprovalLog::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function isReadyForSigning(): bool
    {
        return $this->status === 'approved'
            && $this->approval_level_current >= $this->approval_level_required
            && !empty($this->pdf_path);
    }
}