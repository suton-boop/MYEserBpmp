<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalController extends Controller
{
    public function index(Request $request)
    {
        $eventId = $request->query('event_id');

        $events = Event::orderBy('name')->get(['id', 'name']);

        $certificates = Certificate::query()
            ->with(['event', 'participant'])
            ->where('status', Certificate::STATUS_SUBMITTED)
            ->when($eventId, fn($q) => $q->where('event_id', $eventId))
            ->latest('submitted_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.approvals.index', compact('certificates', 'events', 'eventId'));
    }

    public function rejected(Request $request)
    {
        $eventId = $request->query('event_id');
        $events = Event::orderBy('name')->get(['id', 'name']);

        $certificates = Certificate::query()
            ->with(['event', 'participant', 'submittedBy'])
            ->where('status', Certificate::STATUS_REJECTED)
            ->when($eventId, fn($q) => $q->where('event_id', $eventId))
            ->latest('rejected_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.approvals.rejected', compact('certificates', 'events', 'eventId'));
    }

    // POST /admin/approvals/{certificate}/approve
    public function approve(Certificate $certificate)
    {
        if ($certificate->status !== Certificate::STATUS_SUBMITTED) {
            return back()->with('error', 'Hanya status SUBMITTED yang bisa di-approve.');
        }

        try {
            $year = (int)now()->format('Y');

            $numData = \App\Services\CertificateNumberGenerator::generate($year);

            $certificate->update([
                'status' => Certificate::STATUS_APPROVED,
                'year' => $numData['year'],
                'sequence' => $numData['sequence'],
                'certificate_number' => $numData['certificate_number'],
                'approved_at' => now(),
                'approved_by' => auth()->id(),
            ]);

            return back()->with('success', 'Sertifikat disetujui & nomor dikunci.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menyetujui sertifikat: ' . $e->getMessage());
        }
    }

    // POST /admin/approvals/{certificate}/reject
    public function reject(Request $request, Certificate $certificate)
    {
        if ($certificate->status !== Certificate::STATUS_SUBMITTED) {
            return back()->with('error', 'Hanya status SUBMITTED yang bisa di-reject.');
        }

        $data = $request->validate([
            'rejected_note' => ['required', 'string', 'max:2000'],
        ]);

        $certificate->update([
            'status' => Certificate::STATUS_REJECTED,
            'rejected_at' => now(),
            'rejected_by' => auth()->id(),
            'rejected_note' => $data['rejected_note'],
        ]);

        return back()->with('success', 'Sertifikat ditolak.');
    }

    /**
     * POST /admin/approvals/approve-all
     * Approve semua sertifikat SUBMITTED (opsional filter event_id).
     */
    public function approveAll(Request $request)
    {
        $eventId = $request->input('event_id'); // boleh null

        try {
            $year = (int)now()->format('Y');

            // Ambil semua submitted yang akan diproses (urut by submitted_at biar rapi)
            $query = Certificate::query()
                ->where('status', Certificate::STATUS_SUBMITTED)
                ->when($eventId, fn($q) => $q->where('event_id', $eventId))
                ->orderBy('submitted_at');

            DB::transaction(function () use ($query, $year) {
                $certs = $query->lockForUpdate()->get(); // lock row-row yang akan diproses
                if ($certs->isNotEmpty()) {
                    \App\Services\CertificateNumberGenerator::approveBatch($certs, $year, auth()->id());
                }
            });

            return back()->with('success', 'Approve All berhasil diproses.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menyetujui massal: ' . $e->getMessage());
        }
    }

    /**
     * POST /admin/approvals/reject-all
     * Reject semua sertifikat SUBMITTED (opsional filter event_id).
     */
    public function rejectAll(Request $request)
    {
        $data = $request->validate([
            'event_id' => ['nullable', 'integer', 'exists:events,id'],
            'rejected_note' => ['required', 'string', 'max:2000'],
        ]);

        $eventId = $data['event_id'] ?? null;

        $affected = Certificate::query()
            ->where('status', Certificate::STATUS_SUBMITTED)
            ->when($eventId, fn($q) => $q->where('event_id', $eventId))
            ->update([
            'status' => Certificate::STATUS_REJECTED,
            'rejected_at' => now(),
            'rejected_by' => auth()->id(),
            'rejected_note' => $data['rejected_note'],
        ]);

        return back()->with('success', "Reject All berhasil. Total ditolak: {$affected}");
    }
}