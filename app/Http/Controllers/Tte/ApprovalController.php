<?php

namespace App\Http\Controllers\Tte;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\ApprovalLog;
use App\Domain\Certificates\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApprovalController extends Controller
{
    public function __construct(private AuditLogger $audit)
    {
        $this->middleware(['auth']);
    }

    public function review(Request $request, string $id)
    {
        $request->validate(['note' => ['nullable','string','max:2000']]);

        $cert = Certificate::query()->findOrFail($id);
        if (!in_array($cert->status, ['generated','reviewed'], true)) {
            abort(422, 'Invalid status for review.');
        }

        // RBAC: pastikan role reviewer/approver (gunakan policy/permission Anda)
        $level = max(1, $cert->approval_level_current + 1);

        ApprovalLog::query()->create([
            'id' => (string) Str::uuid(),
            'certificate_id' => $cert->id,
            'level' => $level,
            'action' => 'review',
            'note' => $request->input('note'),
            'acted_by' => $request->user()->id,
            'acted_ip' => $request->ip(),
            'acted_user_agent' => mb_substr((string)$request->userAgent(), 0, 255),
        ]);

        $cert->update(['status' => 'reviewed']);

        $this->audit->log('certificate.reviewed', $cert->id, Certificate::class, [
            'level' => $level
        ], $request->user()->id, $request->ip(), $request->userAgent());

        return response()->json(['data' => $cert]);
    }

    public function approve(Request $request, string $id)
    {
        $request->validate(['note' => ['nullable','string','max:2000']]);

        $cert = Certificate::query()->findOrFail($id);
        if (!in_array($cert->status, ['reviewed','approved'], true)) {
            abort(422, 'Invalid status for approve.');
        }

        $nextLevel = $cert->approval_level_current + 1;

        ApprovalLog::query()->create([
            'id' => (string) Str::uuid(),
            'certificate_id' => $cert->id,
            'level' => $nextLevel,
            'action' => 'approve',
            'note' => $request->input('note'),
            'acted_by' => $request->user()->id,
            'acted_ip' => $request->ip(),
            'acted_user_agent' => mb_substr((string)$request->userAgent(), 0, 255),
        ]);

        $status = ($nextLevel >= $cert->approval_level_required) ? 'approved' : 'reviewed';

        $cert->update([
            'approval_level_current' => $nextLevel,
            'status' => $status,
        ]);

        $this->audit->log('certificate.approved', $cert->id, Certificate::class, [
            'level' => $nextLevel,
            'status' => $status
        ], $request->user()->id, $request->ip(), $request->userAgent());

        return response()->json(['data' => $cert]);
    }

    public function reject(Request $request, string $id)
    {
        $request->validate(['note' => ['required','string','max:2000']]);

        $cert = Certificate::query()->findOrFail($id);

        ApprovalLog::query()->create([
            'id' => (string) Str::uuid(),
            'certificate_id' => $cert->id,
            'level' => max(1, $cert->approval_level_current),
            'action' => 'reject',
            'note' => $request->input('note'),
            'acted_by' => $request->user()->id,
            'acted_ip' => $request->ip(),
            'acted_user_agent' => mb_substr((string)$request->userAgent(), 0, 255),
        ]);

        $cert->update([
            'status' => 'draft',
            'approval_level_current' => 0,
        ]);

        $this->audit->log('certificate.rejected', $cert->id, Certificate::class, [
            'note' => $request->input('note')
        ], $request->user()->id, $request->ip(), $request->userAgent());

        return response()->json(['data' => $cert]);
    }
}