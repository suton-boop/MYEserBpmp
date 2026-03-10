<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class DigitalSignature extends Model
{
    use HasUuids;

    protected $table = 'digital_signatures';

    protected $fillable = [
        'certificate_id',
        'signer_certificate_id',
        'document_hash',
        'signature_base64',
        'signature_algo',
        'tsa_enabled',
        'tsa_at',
        'tsa_nonce',
        'tsa_signature_base64',
        'tsa_signer_code',
        'public_token',
        'signed_at',
        'signed_by',
        'signed_ip',
        'signed_user_agent',

        // appearance
        'is_visible',
        'placement_mode',
        'page',
        'pos_x',
        'pos_y',
        'width',
        'height',
        'sign_reason',
        'sign_location',
        'signature_image_path',
    ];

    protected $casts = [
        'tsa_enabled' => 'boolean',
        'tsa_at' => 'datetime',
        'signed_at' => 'datetime',
        'is_visible' => 'boolean',
        'page' => 'integer',
        'pos_x' => 'integer',
        'pos_y' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    public function certificate()
    {
        return $this->belongsTo(Certificate::class);
    }

    public function signerCertificate()
    {
        return $this->belongsTo(SignerCertificate::class);
    }
}