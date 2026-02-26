<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Event;
use App\Models\Participant;
use App\Services\CertificatePdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CertificateController extends Controller
{
    public function index(Request $request)
    {
        $eventId = $request->query('event_id');
        $q       = trim((string) $request->query('q', ''));
        $status  = trim((string) $request->query('status', ''));

        $events = Event::orderBy('name')->get();

        $participants = Participant::query()
            ->with('event')
            ->when($eventId, fn ($qq) => $qq->where('event_id', $eventId))
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('nik', 'like', "%{$q}%")
                        ->orWhere('institution', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $certMap = collect();
        if ($participants->count() > 0) {
            $certMap = Certificate::query()
                ->whereIn('participant_id', $participants->pluck('id'))
                ->when($eventId, fn ($qq) => $qq->where('event_id', $eventId))
                ->when($status !== '', fn ($qq) => $qq->where('status', $status))
                ->get()
                ->keyBy(fn ($c) => $c->event_id . ':' . $c->participant_id);
        }

        return view('certificates.index', compact('events', 'eventId', 'q', 'status', 'participants', 'certMap'));
    }

    /**
     * Generate Draft (data draft saja, tanpa nomor & tanpa PDF final)
     */
    public function generateOne(Request $request, Participant $participant)
    {
        $data = $request->validate([
            'event_id' => ['required', 'integer', 'exists:events,id'],
        ]);

        $eventId = (int) $data['event_id'];

        if ((int) $participant->event_id !== $eventId) {
            return back()->with('error', 'Peserta tidak sesuai dengan event yang dipilih.');
        }

        $event = Event::with('certificateTemplate')->findOrFail($eventId);

        if (!$event->certificate_template_id || !$event->certificateTemplate) {
            return back()->with('error', 'Event belum memilih Template Sertifikat.');
        }

        if (!(bool) $event->certificateTemplate->is_active) {
            return back()->with('error', 'Template sertifikat untuk event ini Nonaktif. Aktifkan dulu.');
        }

        $existing = Certificate::where('event_id', $eventId)
            ->where('participant_id', $participant->id)
            ->first();

        if ($existing) {
            if (!$existing->verify_token) {
                $existing->update(['verify_token' => $this->makeVerifyToken()]);
            }
            return back()->with('success', "Draft sudah ada. Status: {$existing->status}");
        }

        Certificate::create([
            'event_id'       => $eventId,
            'participant_id' => $participant->id,
            'status'         => Certificate::STATUS_DRAFT,
            'verify_token'   => $this->makeVerifyToken(),
            'created_by'     => auth()->id(),
        ]);

        return back()->with('success', "Draft dibuat untuk {$participant->name}.");
    }

    /**
     * Generate Draft massal
     */
    public function generateAll(Request $request)
    {
        $data = $request->validate([
            'event_id' => ['required', 'integer', 'exists:events,id'],
        ]);

        $eventId = (int) $data['event_id'];

        $event = Event::with('certificateTemplate')->findOrFail($eventId);

        if (!$event->certificate_template_id || !$event->certificateTemplate) {
            return back()->with('error', 'Event belum memilih Template Sertifikat.');
        }

        if (!(bool) $event->certificateTemplate->is_active) {
            return back()->with('error', 'Template sertifikat untuk event ini Nonaktif. Aktifkan dulu.');
        }

        $participants = Participant::where('event_id', $eventId)->get(['id']);

        if ($participants->isEmpty()) {
            return back()->with('error', 'Peserta untuk event ini masih kosong.');
        }

        $existingIds = Certificate::where('event_id', $eventId)
            ->whereIn('participant_id', $participants->pluck('id'))
            ->pluck('participant_id')
            ->all();

        $existingLookup = array_flip($existingIds);
        $created = 0;

        DB::transaction(function () use ($participants, $eventId, &$created, $existingLookup) {
            foreach ($participants as $p) {
                if (isset($existingLookup[$p->id])) continue;

                Certificate::create([
                    'event_id'       => $eventId,
                    'participant_id' => $p->id,
                    'status'         => Certificate::STATUS_DRAFT,
                    'verify_token'   => $this->makeVerifyToken(),
                    'created_by'     => auth()->id(),
                ]);

                $created++;
            }
        });

        return back()->with('success', "Draft dibuat: {$created} peserta (yang belum punya).");
    }

    /**
     * Generate PDF FINAL hanya setelah APPROVED
     */
    public function generatePdfOne(Request $request, Certificate $certificate, CertificatePdfService $pdfService)
    {
        try {
            if ($certificate->status !== Certificate::STATUS_APPROVED) {
                return back()->with('error', 'PDF final hanya bisa dibuat setelah status APPROVED.');
            }

            if (!$certificate->certificate_number || !$certificate->year || !$certificate->sequence) {
                return back()->with('error', 'Nomor sertifikat belum dikunci. Approve dulu.');
            }

            $path = $pdfService->generatePdf($certificate);

            $certificate->update([
                'pdf_path'     => $path,
                'status'       => Certificate::STATUS_FINAL_GENERATED,
                'generated_at' => now(),
            ]);

            return back()->with('success', 'PDF final sertifikat berhasil dibuat.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Generate PDF FINAL massal hanya APPROVED
     */
    public function generatePdfAll(Request $request, CertificatePdfService $pdfService)
    {
        $data = $request->validate([
            'event_id' => ['required', 'integer', 'exists:events,id'],
        ]);

        $eventId = (int) $data['event_id'];

        $certs = Certificate::where('event_id', $eventId)
            ->where('status', Certificate::STATUS_APPROVED)
            ->get();

        if ($certs->isEmpty()) {
            return back()->with('error', 'Tidak ada sertifikat APPROVED untuk dibuatkan PDF.');
        }

        $ok = 0; $fail = 0;

        foreach ($certs as $c) {
            try {
                if (!$c->certificate_number || !$c->year || !$c->sequence) {
                    $fail++;
                    continue;
                }

                $path = $pdfService->generatePdf($c);

                $c->update([
                    'pdf_path'     => $path,
                    'status'       => Certificate::STATUS_FINAL_GENERATED,
                    'generated_at' => now(),
                ]);

                $ok++;
            } catch (\Throwable $e) {
                $fail++;
            }
        }

        return back()->with('success', "Generate PDF final selesai. Berhasil: {$ok}, Gagal: {$fail}");
    }

    public function preview(Certificate $certificate)
    {
        $pdfPath = $this->normalizePdfPath($certificate->pdf_path);

        if (!$pdfPath) return back()->with('error', 'PDF belum tersedia.');
        if (!Storage::disk('public')->exists($pdfPath)) {
            return back()->with('error', 'File PDF tidak ditemukan di storage/public.');
        }

        return response()->file(Storage::disk('public')->path($pdfPath), [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function download(Certificate $certificate)
    {
        $pdfPath = $this->normalizePdfPath($certificate->pdf_path);

        if (!$pdfPath) return back()->with('error', 'PDF belum tersedia.');
        if (!Storage::disk('public')->exists($pdfPath)) {
            return back()->with('error', 'File PDF tidak ditemukan di storage/public.');
        }

        $filename = 'sertifikat-' . ($certificate->certificate_number ?: $certificate->id) . '.pdf';
        $filename = preg_replace('/[^A-Za-z0-9\-\_\.]/', '-', $filename);

        return Storage::disk('public')->download($pdfPath, $filename);
    }

    private function makeVerifyToken(): string
    {
        do {
            $token = (string) Str::uuid();
        } while (Certificate::where('verify_token', $token)->exists());

        return $token;
    }

    private function normalizePdfPath(?string $path): ?string
    {
        $path = trim((string) $path);
        if ($path === '') return null;

        $path = preg_replace('#^storage/#', '', $path);
        $path = preg_replace('#^public/#', '', $path);
        $path = ltrim($path, '/');

        return $path;
    }

 
}