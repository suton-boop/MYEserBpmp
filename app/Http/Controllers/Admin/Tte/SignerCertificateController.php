<?php

namespace App\Http\Controllers\Admin\Tte;

use App\Http\Controllers\Controller;
use App\Models\SignerCertificate;
use App\Domain\Certificates\Services\KeyManagerService;
use App\Domain\Certificates\Services\AuditLogger;
use Illuminate\Http\Request;

class SignerCertificateController extends Controller
{
    public function __construct(
        private KeyManagerService $keys,
        private AuditLogger $audit
    ) {}

    public function index()
    {
        $items = SignerCertificate::query()->latest()->paginate(15);
        return view('admin.tte.signers.index', compact('items'));
    }

    public function create()
    {
        return view('admin.tte.signers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required','string','max:50','unique:signer_certificates,code'],
            'name' => ['required','string','max:150'],
            'public_key_pem' => ['required','string'],
            'private_key_pem' => ['required','string'],
            'valid_from' => ['nullable','date'],
            'valid_to' => ['nullable','date','after_or_equal:valid_from'],
        ]);

        // SECURITY: jangan pernah log private_key_pem
        $cert = $this->keys->createSignerCertificate(
            code: $data['code'],
            name: $data['name'],
            publicKeyPem: $data['public_key_pem'],
            privateKeyPem: $data['private_key_pem'],
            createdBy: $request->user()->id,
            validFrom: $data['valid_from'] ?? null,
            validTo: $data['valid_to'] ?? null,
        );

        $this->audit->log(
            'signer_certificate.created',
            $cert->id,
            SignerCertificate::class,
            ['code' => $cert->code, 'name' => $cert->name],
            $request->user()->id,
            $request->ip(),
            $request->userAgent()
        );

        return redirect()->route('admin.tte.signers.index')->with('success', 'Signer certificate dibuat.');
    }

    public function deactivate(Request \, string \)
    {
        \ = SignerCertificate::query()->findOrFail(\);
        \->keys->deactivate(\->id);

        \->audit->log(
            'signer_certificate.deactivated',
            \->id,
            SignerCertificate::class,
            ['code' => \->code],
            \->user()->id,
            \->ip(),
            \->userAgent()
        );

        return back()->with('success', 'Signer certificate dinonaktifkan.');
    }
}