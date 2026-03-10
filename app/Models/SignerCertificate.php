<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SignerCertificate extends Model
{
    use HasUuids;

    protected $table = 'signer_certificates';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
        'public_key_pem',
        'private_key_encrypted',
        'private_key_fingerprint',
        'is_active',
        'valid_from',
        'valid_to',
        'rotated_from_id',
        'created_by',
        'meta',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
        'meta' => 'array',
    ];

    public function rotatedFrom()
    {
        return $this->belongsTo(self::class , 'rotated_from_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class , 'created_by');
    }
}