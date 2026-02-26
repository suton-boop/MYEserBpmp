<?php

namespace App\Domain\Certificates\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SignerCertificate extends Model
{
    use HasUuids;

    protected $fillable = [
        'code','name','public_key_pem','private_key_encrypted',
        'private_key_fingerprint','is_active','valid_from','valid_to',
        'rotated_from_id','created_by',
    ];

    protected $casts = [
        'is_active' => 'bool',
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
    ];

    public function signatures(): HasMany
    {
        return $this->hasMany(DigitalSignature::class);
    }

    public function rotatedFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'rotated_from_id');
    }
}