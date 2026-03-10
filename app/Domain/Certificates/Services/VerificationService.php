<?php

namespace App\Domain\Certificates\Services;

use App\Models\Certificate;
use App\Models\DigitalSignature;
use App\Models\SignerCertificate;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Storage;

class VerificationService
{
    public function verifyRemote(string $token, string $secret): array
    {
        try {
            $payload = JWT::decode($token, new Key($secret, "HS256"));
            $certId = (int)$payload->sub;

            $cert = Certificate::query()->with(["participant", "event"])->findOrFail($certId);
            $sig = DigitalSignature::query()->where("certificate_id", $cert->id)->firstOrFail();
            $signer = SignerCertificate::query()->findOrFail($sig->signer_certificate_id);

            return [
                "success" => true,
                "certificate" => $cert,
                "signature" => $sig,
                "signer" => $signer,
                "signed_at" => $sig->signed_at,
            ];
        }
        catch (\Exception $e) {
            return [
                "success" => false,
                "message" => $e->getMessage(),
            ];
        }
    }
}
