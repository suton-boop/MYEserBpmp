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
        'appearance_mode',
        'appearance_page',
        'appearance_x',
        'appearance_y',
        'appearance_w',
        'appearance_h',
        'appearance_reason',
        'appearance_location',
    ];

    protected $casts = [
        'tsa_enabled' => 'boolean',
        'tsa_at'      => 'datetime',
        'signed_at'   => 'datetime',
        'appearance_page' => 'integer',
        'appearance_x'    => 'integer',
        'appearance_y'    => 'integer',
        'appearance_w'    => 'integer',
        'appearance_h'    => 'integer',
    ];
}