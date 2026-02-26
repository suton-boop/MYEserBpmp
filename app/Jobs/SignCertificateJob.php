<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SignCertificateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $certificateId;
    public string $signerCertCode;
    public int $signedBy;
    public string $ip;
    public string $userAgent;

    /** @var array{mode:string,page:int,x:int,y:int,w:int,h:int,reason:?string,location:?string} */
    public array $appearance;

    public function __construct(
        int $certificateId,
        string $signerCertCode,
        int $signedBy,
        string $ip,
        string $userAgent,
        array $appearance = []
    ) {
        $this->certificateId = $certificateId;
        $this->signerCertCode = $signerCertCode;
        $this->signedBy = $signedBy;
        $this->ip = $ip;
        $this->userAgent = $userAgent;

        $this->appearance = array_merge([
            'mode' => 'visible',
            'page' => 1,
            'x' => 30,
            'y' => 30,
            'w' => 160,
            'h' => 50,
            'reason' => null,
            'location' => null,
        ], $appearance);
    }

    public function handle(): void
    {
        // TODO: panggil TteService untuk sign & simpan DigitalSignature
        // Pastikan: jika appearance['mode'] === 'hidden' => jangan render image di PDF (tapi signature + token tetap dibuat)

        // Contoh nanti:
        // app(\App\Services\Tte\TteService::class)->signCertificate(
        //   $this->certificateId, $this->signerCertCode, $this->signedBy, $this->ip, $this->userAgent, $this->appearance
        // );
    }
}