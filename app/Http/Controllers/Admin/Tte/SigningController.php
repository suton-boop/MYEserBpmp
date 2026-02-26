<?php

namespace App\Http\Controllers\Admin\Tte;

use App\Http\Controllers\Controller;
use App\Jobs\SignCertificateJob;
use App\Models\Certificate;
use App\Models\Event;
use App\Models\SignerCertificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SigningController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function index(Request $request)
    {
        $q       = trim((string) $request->query('q', ''));
        $eventId = $request->query('event_id');

        $events = Event::query()
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->get(['id', 'name']);

        $signers = SignerCertificate::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        $query = Certificate::query()
            ->with(['event:id,name', 'participant:id,name'])
            ->whereIn('status', ['approved', 'final_generated']);

        if (!empty($eventId) && is_numeric($eventId)) {
            $query->where('event_id', (int) $eventId);
        }

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('certificate_number', 'like', "%{$q}%")
                  ->orWhere('certificate_no', 'like', "%{$q}%")
                  ->orWhereHas('participant', fn ($p) => $p->where('name', 'like', "%{$q}%"))
                  ->orWhereHas('event', fn ($e) => $e->where('name', 'like', "%{$q}%"));
            });
        }

        $certificates = $query->orderByDesc('updated_at')->paginate(20)->withQueryString();

        return view('admin.tte.signing.index', compact('q', 'eventId', 'events', 'signers', 'certificates'));
    }

    public function preview(string $id)
    {
        if (!ctype_digit($id)) {
            return back()->with('error', 'ID sertifikat tidak valid.');
        }

        $cert = Certificate::query()->find((int) $id);
        if (!$cert) {
            return back()->with('error', 'Sertifikat tidak ditemukan.');
        }

        if (!$cert->pdf_path) {
            return back()->with('error', 'PDF belum tersedia untuk sertifikat ini.');
        }

        if (!Storage::disk('public')->exists($cert->pdf_path)) {
            return back()->with('error', 'File PDF tidak ditemukan di storage.');
        }

        return response()->file(
            Storage::disk('public')->path($cert->pdf_path),
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="sertifikat-'.$cert->id.'.pdf"',
            ]
        );
    }

    public function dispatchSingle(Request $request, string $id)
    {
        if (!ctype_digit($id)) {
            return back()->with('error', 'ID sertifikat tidak valid.');
        }

        $validated = $request->validate([
            'signer_certificate_id' => ['required', 'integer', 'exists:signer_certificates,id'],
            'barcode_visible' => ['nullable', 'boolean'],
            'tte_visible' => ['nullable', 'boolean'],
            'appearance_page' => ['nullable', 'integer', 'min:1', 'max:999'],
            'appearance_x' => ['nullable', 'integer', 'min:0', 'max:20000'],
            'appearance_y' => ['nullable', 'integer', 'min:0', 'max:20000'],
            'appearance_w' => ['nullable', 'integer', 'min:10', 'max:20000'],
            'appearance_h' => ['nullable', 'integer', 'min:10', 'max:20000'],
        ]);

        $cert = Certificate::query()->with(['event', 'participant'])->find((int) $id);
        if (!$cert) return back()->with('error', 'Sertifikat tidak ditemukan.');

        if (!in_array($cert->status, ['approved', 'final_generated'], true)) {
            return back()->with('error', 'Status sertifikat tidak valid untuk signing.');
        }

        $signer = SignerCertificate::query()
            ->where('id', (int) $validated['signer_certificate_id'])
            ->where('is_active', true)
            ->first();

        if (!$signer) {
            return back()->with('error', 'Signer tidak ditemukan / tidak aktif.');
        }

        // optional: pastikan ada pdf final dulu
        if (!$cert->pdf_path) {
            return back()->with('error', 'PDF belum tersedia. Generate PDF dulu (final_generated).');
        }

        SignCertificateJob::dispatch(
            certificateId: (int) $cert->id,
            signerCertCode: (string) $signer->code,
            signedBy: (int) $request->user()->id,
            ip: (string) $request->ip(),
            userAgent: (string) $request->userAgent(),
            appearance: [
                'barcode_visible' => (bool) ($validated['barcode_visible'] ?? false),
                'tte_visible' => (bool) ($validated['tte_visible'] ?? false),
                'page' => (int) ($validated['appearance_page'] ?? 1),
                'x' => (int) ($validated['appearance_x'] ?? 0),
                'y' => (int) ($validated['appearance_y'] ?? 0),
                'w' => (int) ($validated['appearance_w'] ?? 200),
                'h' => (int) ($validated['appearance_h'] ?? 80),
            ]
        )->onQueue('tte-signing');

        return back()->with('success', 'Dispatch sign sukses (1 data).');
    }

    public function dispatchBulk(Request $request)
    {
        $validated = $request->validate([
            'signer_certificate_id' => ['required', 'integer', 'exists:signer_certificates,id'],
            'certificate_ids' => ['nullable', 'array'],
            'certificate_ids.*' => ['integer'],

            'barcode_visible' => ['nullable', 'boolean'],
            'tte_visible' => ['nullable', 'boolean'],
            'appearance_page' => ['nullable', 'integer', 'min:1', 'max:999'],
            'appearance_x' => ['nullable', 'integer', 'min:0', 'max:20000'],
            'appearance_y' => ['nullable', 'integer', 'min:0', 'max:20000'],
            'appearance_w' => ['nullable', 'integer', 'min:10', 'max:20000'],
            'appearance_h' => ['nullable', 'integer', 'min:10', 'max:20000'],

            // biar bulk tetap ikut filter halaman (optional)
            'q' => ['nullable', 'string', 'max:100'],
            'event_id' => ['nullable'],
        ]);

        $signer = SignerCertificate::query()
            ->where('id', (int) $validated['signer_certificate_id'])
            ->where('is_active', true)
            ->first();

        if (!$signer) return back()->with('error', 'Signer tidak ditemukan / tidak aktif.');

        $ids = $validated['certificate_ids'] ?? [];
        $ids = array_slice(array_values(array_unique(array_map('intval', $ids))), 0, 20);

        if (count($ids) === 0) {
            return back()->with('error', 'Pilih minimal 1 sertifikat (checkbox).');
        }

        $certs = Certificate::query()
            ->whereIn('id', $ids)
            ->whereIn('status', ['approved', 'final_generated'])
            ->get(['id', 'pdf_path']);

        if ($certs->count() === 0) {
            return back()->with('error', 'Tidak ada sertifikat valid untuk dispatch.');
        }

        $appearance = [
            'barcode_visible' => (bool) ($validated['barcode_visible'] ?? false),
            'tte_visible' => (bool) ($validated['tte_visible'] ?? false),
            'page' => (int) ($validated['appearance_page'] ?? 1),
            'x' => (int) ($validated['appearance_x'] ?? 0),
            'y' => (int) ($validated['appearance_y'] ?? 0),
            'w' => (int) ($validated['appearance_w'] ?? 200),
            'h' => (int) ($validated['appearance_h'] ?? 80),
        ];

        $count = 0;
        foreach ($certs as $c) {
            if (!$c->pdf_path) {
                continue; // skip yang belum ada PDF
            }

            SignCertificateJob::dispatch(
                certificateId: (int) $c->id,
                signerCertCode: (string) $signer->code,
                signedBy: (int) $request->user()->id,
                ip: (string) $request->ip(),
                userAgent: (string) $request->userAgent(),
                appearance: $appearance
            )->onQueue('tte-signing');

            $count++;
        }

        if ($count === 0) {
            return back()->with('error', 'Semua data terpilih belum punya PDF. Generate PDF dulu.');
        }

        return back()->with('success', "Bulk dispatch sukses: {$count} data (maks 20).");
    }

    public function signNow(Request $request, string $id)
    {
        return $this->dispatchSingle($request, $id);
    }
}