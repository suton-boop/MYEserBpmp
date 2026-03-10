<?php

namespace App\Domain\Certificates\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    public function log(
        string $eventType,
        ?string $subjectId = null,
        ?string $subjectType = null,
        array $metadata = [],
        ?int $actorId = null,
        ?string $ip = null,
        ?string $userAgent = null
    ): AuditLog {
        $prev = AuditLog::query()->latest('created_at')->first();
        $prevHash = $prev?->hash;

        $actorId ??= Auth::id();

        $payload = [
            'event_type' => $eventType,
            'subject_id' => $subjectId,
            'subject_type' => $subjectType,
            'actor_id' => $actorId,
            'actor_ip' => $ip,
            'actor_user_agent' => $userAgent,
            'metadata' => $metadata,
            'prev_hash' => $prevHash,
            'ts' => now()->toISOString(),
        ];

        // SECURITY: hash chain mencegah modifikasi log tanpa terdeteksi.
        $hash = hash('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES));

        return AuditLog::query()->create([
            'event_type' => $eventType,
            'subject_id' => $subjectId,
            'subject_type' => $subjectType,
            'actor_id' => $actorId,
            'actor_ip' => $ip,
            'actor_user_agent' => $userAgent,
            'metadata' => $metadata,
            'prev_hash' => $prevHash,
            'hash' => $hash,
        ]);
    }
}