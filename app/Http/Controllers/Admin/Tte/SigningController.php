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
        $q = trim((string)$request->query('q', ''));
        $eventId = $request->query('event_id');
        $events = Event::query()->orderByDesc('start_date')->orderByDesc('id')->get(['id', 'name']);
        $signers = SignerCertificate::query()->where('is_active', true)->orderBy('name')->get(['id', 'code', 'name']);

        $query = Certificate::query()->with(['event:id,name', 'participant:id,name'])
            ->whereIn('status', ['approved', 'final_generated', 'gagal_tte', 'Gagal_tte']);
        if (!empty($eventId) && is_numeric($eventId)) {
            $query->where('event_id', (int)$eventId);
        }
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('certificate_number', 'like', "%{$q}%")
                    ->orWhere('certificate_no', 'like', "%{$q}%")
                    ->orWhereHas('participant', fn($p) => $p->where('name', 'like', "%{$q}%"))
                    ->orWhereHas('event', fn($e) => $e->where('name', 'like', "%{$q}%"));
            });
        }

        $certificates = $query->orderByDesc('updated_at')->paginate(20)->withQueryString();
        return view('admin.tte.signing.index', compact('q', 'eventId', 'events', 'signers', 'certificates'));
    }

    public function preview(string $id)
    {
        if (!ctype_digit($id))
            return back()->with('error', 'ID sertifikat tidak valid.');
        $cert = Certificate::query()->find((int)$id);
        if (!$cert)
            return back()->with('error', 'Sertifikat tidak ditemukan.');
        if (!$cert->pdf_path)
            return back()->with('error', 'PDF belum tersedia untuk sertifikat ini.');
        if (!Storage::disk('public')->exists($cert->pdf_path))
            return back()->with('error', 'File PDF tidak ditemukan di storage.');

        return response()->file(
            Storage::disk('public')->path($cert->pdf_path),
        [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="sertifikat-' . $cert->id . '.pdf"',
        ]
        );
    }

    public function dispatchSingle(Request $request, string $id)
    {
        if (!ctype_digit($id))
            return back()->with('error', 'ID sertifikat tidak valid.');

        $validated = $request->validate([
            'signer_certificate_id' => ['required', 'string'],
            'barcode_visible' => ['nullable', 'boolean'],
            'tte_visible' => ['nullable', 'boolean'],
            'appearance_page' => ['nullable', 'integer', 'min:1', 'max:999'],
            'appearance_x' => ['nullable', 'integer', 'min:0', 'max:20000'],
            'appearance_y' => ['nullable', 'integer', 'min:0', 'max:20000'],
            'appearance_w' => ['nullable', 'integer', 'min:5', 'max:20000'],
            'appearance_h' => ['nullable', 'integer', 'min:5', 'max:20000'],
            'placements' => ['nullable', 'array'],
            'schedule_date' => ['nullable', 'date'],
        ]);

        $cert = Certificate::query()->with(['event', 'participant'])->find((int)$id);
        if (!$cert)
            return back()->with('error', 'Sertifikat tidak ditemukan.');
        if (!in_array(strtolower($cert->status), ['approved', 'final_generated', 'gagal_tte', 'scheduled'], true))
            return back()->with('error', 'Status tidak valid.');

        $signer = SignerCertificate::query()->where('id', $validated['signer_certificate_id'])->where('is_active', true)->first();
        if (!$signer)
            return back()->with('error', 'Signer tidak ditemukan atau tidak aktif.');
        if (!$cert->pdf_path)
            return back()->with('error', 'PDF belum tersedia. Generate PDF dulu (final_generated).');

        $placements = $validated['placements'] ?? [
            [
                'page' => (int)($validated['appearance_page'] ?? 1),
                'x' => (int)($validated['appearance_x'] ?? 0),
                'y' => (int)($validated['appearance_y'] ?? 0),
                'w' => (int)($validated['appearance_w'] ?? 200),
                'h' => (int)($validated['appearance_h'] ?? 80),
                'barcode_visible' => (bool)($validated['barcode_visible'] ?? false),
                'tte_visible' => (bool)($validated['tte_visible'] ?? false),
            ]
        ];

        // Logic Identifikasi Tanggal TTE (Sesuai PDF)
        $isDynamic  = (bool) $cert->event->is_date_per_participant;
        $targetDate = $isDynamic 
                    ? ($cert->participant->custom_date ?? $cert->event->signing_date ?? $cert->event->start_date)
                    : ($cert->event->signing_date ?? $cert->event->start_date);
        
        $endDate = $cert->event->end_date;

        $isManualSchedule = !empty($validated['schedule_date']);
        $manualSchedule = $isManualSchedule ? \Carbon\Carbon::parse($validated['schedule_date']) : null;

        // 1. Validasi Logika: Strict Date (Opsional)
        if (!$isManualSchedule) {
            $isStrict = \App\Models\Setting::getValue('strict_tte_date', false);
            if ($isStrict) {
                $nowDate = now()->startOfDay();
                if ($targetDate && $targetDate->startOfDay()->isAfter($nowDate)) {
                    return back()->with('error', 'Validasi ketat aktif: Sertifikat belum dapat di-TTE. Tanggal pada sertifikat ('.$targetDate->format('d/m/Y').') melebih hari ini.');
                }
            }
        }

        // 2. Logic Antrian (Queue with Delay)
        $now = now();
        $scheduledTime = null;

        if ($isManualSchedule && $manualSchedule->isAfter($now)) {
            $scheduledTime = $manualSchedule;
        } elseif (!$isManualSchedule && $targetDate && $targetDate->isAfter(now()->addDay()->endOfDay())) {
            $scheduledTime = $targetDate->copy()->startOfDay()->addMinute(); // 00:01
        }

        $job = new SignCertificateJob(
            (int)$cert->id,
            (string)$signer->code,
            (int)$request->user()->id,
            (string)$request->ip(),
            (string)$request->userAgent(),
            ['placements' => $placements]
        );

        if ($scheduledTime) {
            $delay = $now->diffInSeconds($scheduledTime, false);
            if ($delay > 0) {
                $cert->update([
                    'status' => Certificate::STATUS_SCHEDULED,
                    'scheduled_signer_certificate_id' => $signer->id,
                    'scheduled_appearance' => $placements,
                    'scheduled_at' => $scheduledTime,
                ]);
                dispatch($job->onQueue('tte-signing'))->delay($delay);
                return back()->with('success', 'Sertifikat dijadwalkan otomatis pada ' . $scheduledTime->format('d-m-Y H:i') . ' (Menunggu hari H kegiatan/penandatanganan).');
            }
        }

        // Jika sudah masuk waktunya, langsung proses
        $cert->update(['status' => 'proses_tte']); // Update status segera agar hilang dari list
        dispatch($job->onQueue('tte-signing'));
        return back()->with('success', 'Dispatch sign sukses (1 data).');
    }

    public function dispatchBulk(Request $request)
    {
        $validated = $request->validate([
            'signer_certificate_id' => ['required', 'string'],
            'certificate_ids' => ['nullable', 'array'],
            'certificate_ids.*' => ['integer'],
            'barcode_visible' => ['nullable', 'boolean'],
            'tte_visible' => ['nullable', 'boolean'],
            'appearance_page' => ['nullable', 'integer', 'min:1', 'max:999'],
            'appearance_x' => ['nullable', 'integer', 'min:0', 'max:20000'],
            'appearance_y' => ['nullable', 'integer', 'min:0', 'max:20000'],
            'appearance_w' => ['nullable', 'integer', 'min:5', 'max:20000'],
            'appearance_h' => ['nullable', 'integer', 'min:5', 'max:20000'],
            'q' => ['nullable', 'string', 'max:100'],
            'event_id' => ['nullable'],
            'placements' => ['nullable', 'array'],
            'schedule_date' => ['nullable', 'date'],
        ]);

        $signer = SignerCertificate::query()->where('id', $validated['signer_certificate_id'])->where('is_active', true)->first();
        if (!$signer)
            return back()->with('error', 'Signer tidak ditemukan / tidak aktif.');

        $ids = $validated['certificate_ids'] ?? [];
        // Batasi maksimal 50 sertifikat per klik untuk menjaga kestabilan API TTE
        $ids = array_slice(array_values(array_unique(array_map('intval', $ids))), 0, 50);
        if (count($ids) === 0)
            return back()->with('error', 'Pilih minimal 1 sertifikat (checkbox).');

        $certs = Certificate::query()->with(['event', 'participant'])->whereIn('id', $ids)
            ->whereIn('status', ['approved', 'final_generated', 'gagal_tte', 'Gagal_tte', 'scheduled'])
            ->get();
        if ($certs->count() === 0)
            return back()->with('error', 'Tidak ada sertifikat valid untuk dispatch.');

        $placements = $validated['placements'] ?? [
            [
                'page' => (int)($validated['appearance_page'] ?? 1),
                'x' => (int)($validated['appearance_x'] ?? 0),
                'y' => (int)($validated['appearance_y'] ?? 0),
                'w' => (int)($validated['appearance_w'] ?? 200),
                'h' => (int)($validated['appearance_h'] ?? 80),
                'barcode_visible' => (bool)($validated['barcode_visible'] ?? false),
                'tte_visible' => (bool)($validated['tte_visible'] ?? false),
            ]
        ];

        $appearance = ['placements' => $placements];

        $countSuccess = 0;
        $countScheduled = 0;
        $countError = 0;

        foreach ($certs as $c) {
            if (!$c->pdf_path)
                continue;

            $isDynamic  = (bool) $c->event->is_date_per_participant;
            $targetDate = $isDynamic 
                        ? ($c->participant->custom_date ?? $c->event->signing_date ?? $c->event->start_date)
                        : ($c->event->signing_date ?? $c->event->start_date);
            
            $endDate = $c->event->end_date;

            $isManualSchedule = !empty($validated['schedule_date']);
            $manualSchedule = $isManualSchedule ? \Carbon\Carbon::parse($validated['schedule_date']) : null;

            // Validasi: Strict Date (Opsional)
            if (!$isManualSchedule) {
                $isStrict = \App\Models\Setting::getValue('strict_tte_date', false);
                if ($isStrict) {
                    $nowDate = now()->startOfDay();
                    if ($targetDate && $targetDate->startOfDay()->isAfter($nowDate)) {
                        $countError++;
                        continue;
                    }
                }
            }

            $now = now();
            $scheduledTime = null;
            if ($isManualSchedule && $manualSchedule->isAfter($now)) {
                $scheduledTime = $manualSchedule;
            } elseif (!$isManualSchedule && $targetDate && $targetDate->isAfter(now()->addDay()->endOfDay())) {
                $scheduledTime = $targetDate->copy()->startOfDay()->addMinute();
            }

            $job = new SignCertificateJob(
                (int)$c->id,
                (string)$signer->code,
                (int)$request->user()->id,
                (string)$request->ip(),
                (string)$request->userAgent(),
                $appearance
            );

            if ($scheduledTime) {
                $delay = $now->diffInSeconds($scheduledTime, false);
                if ($delay > 0) {
                    $c->update([
                        'status' => Certificate::STATUS_SCHEDULED,
                        'scheduled_signer_certificate_id' => $signer->id,
                        'scheduled_appearance' => $placements,
                        'scheduled_at' => $scheduledTime,
                    ]);
                    dispatch($job->onQueue('tte-signing'))->delay($delay);
                    $countScheduled++;
                    continue;
                }
            }

            $c->update(['status' => 'proses_tte']); // Update status segera agar hilang dari list
            dispatch($job->onQueue('tte-signing'));
            $countSuccess++;
        }

        $msg = "Proses Berhasil.";
        if ($countSuccess > 0) $msg .= " Segera diproses: {$countSuccess}.";
        if ($countScheduled > 0) $msg .= " Dijadwalkan (Antrian): {$countScheduled}.";
        if ($countError > 0) $msg .= " Gagal Validasi Tanggal: {$countError}.";

        return back()->with('success', $msg);
    }

    public function signNow(Request $request, string $id)
    {
        return $this->dispatchSingle($request, $id);
    }
}
