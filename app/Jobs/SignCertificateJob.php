<?php
namespace App\Jobs;

use App\Models\Certificate;
use App\Models\DigitalSignature;
use App\Services\Tte\TteService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SignCertificateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 120;

    public function __construct(
        public int $certificateId,
        public string $signerCertCode,
        public int $signedBy,
        public string $ip,
        public string $userAgent,
        public array $appearance = []
    ) {}

    public function handle(TteService $tteService): void
    {
        Log::info('JOB_START_SIGN', [
            'cert_id' => $this->certificateId,
            'queue' => $this->queue,
            'queue_default' => config('queue.default'),
        ]);

        try {
            $certificate = Certificate::query()->findOrFail($this->certificateId);

            // Jika sudah signed (misal diproses manual saat queue masih jalan), skip
            if ($certificate->status === 'signed') {
                Log::info('JOB_SKIPPED_ALREADY_SIGNED', ['cert_id' => $this->certificateId]);
                return;
            }

            $certificate->update(['status' => 'proses_tte']);

            $result = $tteService->signCertificate(
                certificateId: $this->certificateId,
                signerCertCode: $this->signerCertCode,
                signedBy: $this->signedBy,
                ip: $this->ip,
                userAgent: $this->userAgent,
                appearance: $this->appearance
            );

            DigitalSignature::query()->create([
                'certificate_id'     => $this->certificateId,
                'signer_certificate_id' => \App\Models\SignerCertificate::where('code', $this->signerCertCode)->first()?->id,
                'signed_by'          => $this->signedBy,
                'document_hash'      => str_repeat('0', 64),
                'signature_base64'   => base64_encode('DUMMY_SIGNATURE_DATA_FROM_LOCAL_TTE'),
                'public_token'       => $result['token'] ?? null,
                'signed_at'          => now(),
                'signed_ip'          => $this->ip,
                'signed_user_agent'  => $this->userAgent,
                'is_visible' => ($this->appearance['tte_visible'] ?? true),
                'page' => (int)($this->appearance['page'] ?? 1),
                'pos_x'    => (int)($this->appearance['x'] ?? 0),
                'pos_y'    => (int)($this->appearance['y'] ?? 0),
                'width'    => (int)($this->appearance['w'] ?? 200),
                'height'    => (int)($this->appearance['h'] ?? 80),
            ]);

            $certificate->update([
                'status'    => 'signed',
                'signed_at' => now(),
            ]);

            Log::info('JOB_DONE_SIGN', ['cert_id' => $this->certificateId]);

        } catch (\Throwable $e) {
            Log::error('TTE Job Failed', [
                'certificate_id' => $this->certificateId,
                'error' => $e->getMessage(),
            ]);

            Certificate::query()
                ->where('id', $this->certificateId)
                ->update(['status' => 'gagal_tte']);

            throw $e;
        }
    }
}
