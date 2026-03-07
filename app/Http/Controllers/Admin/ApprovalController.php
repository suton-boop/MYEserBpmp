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

    // POST /admin/approvals/{certificate}/approve
    public function approve(Certificate $certificate)
    {
        if ($certificate->status !== Certificate::STATUS_SUBMITTED) {
            return back()->with('error', 'Hanya status SUBMITTED yang bisa di-approve.');
        }

        DB::transaction(function () use ($certificate) {

            $year = (int)now()->format('Y');

            $maxSeq = Certificate::where('year', $year)
                ->lockForUpdate()
                ->max('sequence');

            $nextSeq = ((int)$maxSeq) + 1;

            $prefix = 'Sertifikat/BPMP.Kaltim/' . $year;
            $no = str_pad((string)$nextSeq, 5, '0', STR_PAD_LEFT) . '/' . $prefix;

            $certificate->update([
                'status' => Certificate::STATUS_APPROVED,
                'year' => $year,
                'sequence' => $nextSeq,
                'certificate_number' => $no,
                'approved_at' => now(),
                'approved_by' => auth()->id(),
            ]);
        });

        return back()->with('success', 'Sertifikat disetujui & nomor dikunci.');
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

        DB::transaction(function () use ($eventId, $request) {

            $year = (int)now()->format('Y');

            // Ambil semua submitted yang akan diproses (urut by submitted_at biar rapi)
            $query = Certificate::query()
                ->where('status', Certificate::STATUS_SUBMITTED)
                ->when($eventId, fn($q) => $q->where('event_id', $eventId))
                ->orderBy('submitted_at');

            $certs = $query->lockForUpdate()->get(); // lock row-row yang akan diproses

            if ($certs->isEmpty()) {
                // throw supaya transaction tetap aman, tapi kita handle di luar dengan pesan
                // (lebih sederhana: return saja)
                return;
            }

            // Generate sequence global per tahun
            $maxSeq = Certificate::where('year', $year)
                ->lockForUpdate()
                ->max('sequence');

            $seq = (int)$maxSeq;
            $prefix = 'Sertifikat/BPMP.Kaltim/' . $year;

            foreach ($certs as $c) {
                $seq++;
                $no = str_pad((string)$seq, 5, '0', STR_PAD_LEFT) . '/' . $prefix;

                $c->update([
                    'status' => Certificate::STATUS_APPROVED,
                    'year' => $year,
                    'sequence' => $seq,
                    'certificate_number' => $no,
                    'approved_at' => now(),
                    'approved_by' => auth()->id(),
                ]);
            }
        });

        return back()->with('success', 'Approve All berhasil diproses.');
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