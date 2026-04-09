<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProcessScheduledTte extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tte:process-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process certificates that are scheduled for TTE at their designated time.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for scheduled certificates to process...');

        $now = now();
        $certificates = \App\Models\Certificate::where('status', \App\Models\Certificate::STATUS_SCHEDULED)
            ->where('scheduled_at', '<=', $now)
            ->whereNotNull('scheduled_signer_certificate_id')
            ->get();

        if ($certificates->isEmpty()) {
            $this->comment('No matching certificates found (Status: scheduled, Time: <= ' . $now->toDateTimeString() . ').');
            return;
        }

        $this->info("Found {$certificates->count()} certificates to process.");

        foreach ($certificates as $certificate) {
            $this->info("Processing Certificate #{$certificate->id} for {$certificate->participant->name}");

            try {
                $signer = \App\Models\SignerCertificate::find($certificate->scheduled_signer_certificate_id);
                
                if (!$signer || !$signer->is_active) {
                    $this->error("Signer not found or inactive for Certificate #{$certificate->id}");
                    continue;
                }

                // Prepare the job
                $job = new \App\Jobs\SignCertificateJob(
                    (int)$certificate->id,
                    (string)$signer->code,
                    (int)($certificate->approved_by ?? $certificate->created_by ?? 1),
                    '127.0.0.1',
                    'Artisan Command (ProcessScheduledTte)',
                    ['placements' => $certificate->scheduled_appearance]
                );

                // Update status to prevent double processing
                $certificate->update(['status' => 'proses_tte']);

                // Dispatch to queue
                dispatch($job->onQueue('tte-signing'));

                $this->info("Successfully dispatched Certificate #{$certificate->id}");
            } catch (\Exception $e) {
                $this->error("Failed to process Certificate #{$certificate->id}: " . $e->getMessage());
            }
        }

        $this->info('Finished processing scheduled certificates.');
    }
}
