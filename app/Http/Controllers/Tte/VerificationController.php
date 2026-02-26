<?php

namespace App\Http\Controllers\Tte;

use App\Http\Controllers\Controller;
use App\Domain\Certificates\Models\DigitalSignature;
use App\Domain\Certificates\Services\VerificationService;
use App\Domain\Certificates\Services\AuditLogger;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function __construct(
        private VerificationService $verifier,
        private AuditLogger $audit
    ) {}

    // Public verification page (signed URL + JWT in QR + rate limit + replay protection middleware)
    public function show(Request $request, string $token)
    {
        $sig = DigitalSignature::query()->where('public_token', $token)->with('certificate')->firstOrFail();

        $jwt = (string) $request->query('jwt', '');
        $jwtCheck = $jwt ? $this->verifier->validateQrJwt($jwt, $token, $sig->certificate_id) : ['ok' => false, 'claims' => null];

        $result = $this->verifier->verifySignatureRecord($sig);

        $this->audit->log('public.verify', $sig->certificate_id, $sig->certificate::class, [
            'token' => $token,
            'jwt_ok' => $jwtCheck['ok'],
            'overall_valid' => $result['overall_valid'],
        ], null, $request->ip(), $request->userAgent());

        return response()->json([
            'jwt' => $jwtCheck,
            'result' => $result,
        ]);
    }
}