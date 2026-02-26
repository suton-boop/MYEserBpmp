<?php

namespace App\Domain\Certificates\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigitalSignature extends Model
{
    use HasUuids;

    protected $fillable = [
        'certificate_id','signer_certificate_id',
        'document_hash','signature_base64','signature_algo',
        'tsa_enabled','tsa_at','tsa_nonce','tsa_signature_base64','tsa_signer_code',
        'public_token','signed_at','signed_by','signed_ip','signed_user_agent',
    ];

    protected $casts = [
        'tsa_enabled' => 'bool',
        'tsa_at' => 'datetime',
        'signed_at' => 'datetime',
    ];

    public function certificate(): BelongsTo
    {
        return $this->belongsTo(Certificate::class);
    }

    public function signerCertificate(): BelongsTo
    {
        return $this->belongsTo(SignerCertificate::class);
    }

    public function signer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'signed_by');
    }
}