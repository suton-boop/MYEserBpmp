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
use App\Jobs\GenerateCertificatePdfJob;

class CertificateController extends Controller
{
    public function index(Request $request)
    {
        $eventId = $request->query('event_id');
        $q = trim((string)$request->query('q', ''));
        $status = trim((string)$request->query('status', ''));
        $sortBy = $request->query('sort', 'latest');

        $events = Event::orderBy('name')->get();

        $participants = Participant::query()
            ->with('event')
            ->when($eventId, fn($qq) => $qq->where('event_id', $eventId))
            ->when($q !== '', function ($qq) use ($q) {
            $qq->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('nik', 'like', "%{$q}%")
                        ->orWhere('institution', 'like', "%{$q}%");
                }
                );
            })
            ->when($sortBy === 'name_asc', fn($qq) => $qq->orderBy('name', 'asc'))
            ->when($sortBy === 'name_desc', fn($qq) => $qq->orderBy('name', 'desc'))
            ->when($sortBy === 'oldest', fn($qq) => $qq->orderBy('id', 'asc'))
            ->when($sortBy === 'latest' || !$sortBy, fn($qq) => $qq->latest())
            ->paginate(10)
            ->withQueryString();

        $certMap = collect();
        if ($participants->count() > 0) {
            $certMap = Certificate::query()
                ->whereIn('participant_id', $participants->pluck('id'))
                ->when($eventId, fn($qq) => $qq->where('event_id', $eventId))
                ->when($status !== '', fn($qq) => $qq->where('status', $status))
                ->get()
                ->keyBy(fn($c) => $c->event_id . ':' . $c->participant_id);
        }

        return view('certificates.index', compact('events', 'eventId', 'q', 'status', 'participants', 'certMap', 'sortBy'));
    }

    public function published(Request $request)
    {
        $eventId = $request->query('event_id');
        $q = trim((string)$request->query('q', ''));
        $status = $request->query('status');
        $sortBy = $request->query('sort', 'latest');

        $events = Event::orderBy('name')->get();

        $certificates = Certificate::query()
            ->with(['event', 'participant'])
            ->whereIn('status', [Certificate::STATUS_SIGNED, 'terbit', Certificate::STATUS_SENT]) // include terbit and signed logic
            ->when($eventId, fn($qq) => $qq->where('event_id', $eventId))
            ->when($q !== '', function ($qq) use ($q) {
            $qq->whereHas('participant', function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('nik', 'like', "%{$q}%")
                        ->orWhere('institution', 'like', "%{$q}%");
                }
                )->orWhere('certificate_number', 'like', "%{$q}%");
            })
            ->when($sortBy === 'name_asc', function ($qq) {
            $qq->select('certificates.*')
                ->join('participants', 'certificates.participant_id', '=', 'participants.id')
                ->orderBy('participants.name', 'asc');
        })
            ->when($sortBy === 'name_desc', function ($qq) {
            $qq->select('certificates.*')
                ->join('participants', 'certificates.participant_id', '=', 'participants.id')
                ->orderBy('participants.name', 'desc');
        })
            ->when($sortBy === 'oldest', fn($qq) => $qq->orderBy('id', 'asc'))
            ->when($sortBy === 'latest' || !$sortBy, fn($qq) => $qq->latest())
            ->paginate(10)
            ->withQueryString();

        return view('certificates.published', compact('events', 'eventId', 'q', 'certificates', 'sortBy'));
    }

    public function exportPublished(Request $request)
    {
        $eventId = $request->query('event_id');
        $q = trim((string)$request->query('q', ''));
        $sortBy = $request->query('sort', 'latest');

        $query = Certificate::query()
            ->with(['event', 'participant'])
            ->whereIn('status', [Certificate::STATUS_SIGNED, 'terbit', Certificate::STATUS_SENT])
            ->when($eventId, fn($qq) => $qq->where('event_id', $eventId))
            ->when($q !== '', function ($qq) use ($q) {
            $qq->whereHas('participant', function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('nik', 'like', "%{$q}%")
                        ->orWhere('institution', 'like', "%{$q}%");
                }
                )->orWhere('certificate_number', 'like', "%{$q}%");
            })
            ->when($sortBy === 'name_asc', function ($qq) {
            $qq->select('certificates.*')
                ->join('participants', 'certificates.participant_id', '=', 'participants.id')
                ->orderBy('participants.name', 'asc');
        })
            ->when($sortBy === 'name_desc', function ($qq) {
            $qq->select('certificates.*')
                ->join('participants', 'certificates.participant_id', '=', 'participants.id')
                ->orderBy('participants.name', 'desc');
        })
            ->when($sortBy === 'oldest', fn($qq) => $qq->orderBy('id', 'asc'))
            ->when($sortBy === 'latest' || !$sortBy, fn($qq) => $qq->latest());

        $certificates = $query->get();

        $fileName = 'export-sertifikat-terbit-' . date('Ymd_His') . '.xls';

        $headers = [
            "Content-type" => "application/vnd.ms-excel",
            "Content-Disposition" => "attachment; filename=\"$fileName\"",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($certificates) {
            // Kita menggunakan format tabel HTML berstruktur agar otomatis 
            // diformat oleh Microsoft Excel dengan baik dan mendeteksi <a href>.
            echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
            echo '<head><meta http-equiv="content-type" content="application/vnd.ms-excel; charset=UTF-8"></head>';
            echo '<body>';
            echo '<table border="1">';
            echo '<tr>
                    <th style="background-color:#dcdcdc;">No</th>
                    <th style="background-color:#dcdcdc;">Nomor Sertifikat</th>
                    <th style="background-color:#dcdcdc;">Peserta (Nama)</th>
                    <th style="background-color:#dcdcdc;">Email</th>
                    <th style="background-color:#dcdcdc;">Instansi</th>
                    <th style="background-color:#dcdcdc;">Event/Program</th>
                    <th style="background-color:#dcdcdc;">Tanggal Terbit (Signed At)</th>
                    <th style="background-color:#dcdcdc;">Link Unduhan Publik</th>
                  </tr>';

            $no = 1;
            foreach ($certificates as $cert) {
                $publicLink = $cert->verify_token ? route('public.verify.show', $cert->verify_token) : '';
                $linkActive = $publicLink ? '<a href="' . $publicLink . '" target="_blank">' . $publicLink . '</a>' : '-';

                // Mengakali nomor sertifikat agar otomatis terbaca sebagai text, tidak jadi e-science
                $nomor = $cert->certificate_number ?? '-';

                echo '<tr>';
                echo '<td>' . $no++ . '</td>';
                echo '<td style="mso-number-format:\'@\'">' . $nomor . '</td>';
                echo '<td>' . htmlspecialchars($cert->participant->name ?? '-') . '</td>';
                echo '<td>' . htmlspecialchars($cert->participant->email ?? '-') . '</td>';
                echo '<td>' . htmlspecialchars($cert->participant->institution ?? '-') . '</td>';
                echo '<td>' . htmlspecialchars($cert->event->name ?? '-') . '</td>';
                echo '<td>' . ($cert->signed_at ? $cert->signed_at->format('Y-m-d H:i') : '-') . '</td>';
                echo '<td>' . $linkActive . '</td>';
                echo '</tr>';
            }

            echo '</table>';
            echo '</body>';
            echo '</html>';
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Generate Draft (data draft saja, tanpa nomor & tanpa PDF final)
     */
    public function generateOne(Request $request, Participant $participant)
    {
        $data = $request->validate([
            'event_id' => ['required', 'integer', 'exists:events,id'],
        ]);

        $eventId = (int)$data['event_id'];

        if ((int)$participant->event_id !== $eventId) {
            return back()->with('error', 'Peserta tidak sesuai dengan event yang dipilih.');
        }

        $event = Event::with('certificateTemplate')->findOrFail($eventId);

        if (!$event->certificate_template_id || !$event->certificateTemplate) {
            return back()->with('error', 'Event belum memilih Template Sertifikat.');
        }

        if (!(bool)$event->certificateTemplate->is_active) {
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
            'event_id' => $eventId,
            'participant_id' => $participant->id,
            'status' => Certificate::STATUS_DRAFT,
            'verify_token' => $this->makeVerifyToken(),
            'created_by' => auth()->id(),
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

        $eventId = (int)$data['event_id'];

        $event = Event::with('certificateTemplate')->findOrFail($eventId);

        if (!$event->certificate_template_id || !$event->certificateTemplate) {
            return back()->with('error', 'Event belum memilih Template Sertifikat.');
        }

        if (!(bool)$event->certificateTemplate->is_active) {
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
                if (isset($existingLookup[$p->id]))
                    continue;

                Certificate::create([
                    'event_id' => $eventId,
                    'participant_id' => $p->id,
                    'status' => Certificate::STATUS_DRAFT,
                    'verify_token' => $this->makeVerifyToken(),
                    'created_by' => auth()->id(),
                ]);

                $created++;
            }
        });

        return back()->with('success', "Draft dibuat: {$created} peserta (yang belum punya).");
    }

    /**
     * Generate PDF FINAL hanya setelah APPROVED
     */
    public function generatePdfOne(Request $request, Certificate $certificate)
    {
        try {
            if ($certificate->status !== Certificate::STATUS_APPROVED) {
                return back()->with('error', 'PDF final hanya bisa dibuat setelah status APPROVED.');
            }

            if (!$certificate->certificate_number || !$certificate->year || !$certificate->sequence) {
                return back()->with('error', 'Nomor sertifikat belum dikunci. Approve dulu.');
            }

            $certificate->update(['status' => Certificate::STATUS_GENERATING]);
            GenerateCertificatePdfJob::dispatch($certificate);

            return back()->with('success', 'Perintah pembuatan PDF Certificate telah dimasukkan ke dalam antrean (Queue). Silakan refresh halaman beberapa saat lagi.');
        }
        catch (\Throwable $e) {
            return back()->with('error', 'Gagal memproses ke antrean: ' . $e->getMessage());
        }
    }

    /**
     * Generate PDF FINAL massal hanya APPROVED
     */
    public function generatePdfAll(Request $request)
    {
        $data = $request->validate([
            'event_id' => ['required', 'integer', 'exists:events,id'],
        ]);

        $eventId = (int)$data['event_id'];

        // Ambil sertifikat yang APPROVED saja
        $query = Certificate::where('event_id', $eventId)
            ->where('status', Certificate::STATUS_APPROVED);

        $total = $query->count();

        if ($total === 0) {
            return back()->with('error', 'Tidak ada sertifikat APPROVED untuk dibuatkan PDF.');
        }

        // Batasi maksimal 50 data per klik untuk kestabilan server Hostinger
        $limit = 50;
        $certs = $query->limit($limit)->get();

        $dispatched = 0;
        foreach ($certs as $c) {
            if (!$c->certificate_number || !$c->year || !$c->sequence) {
                continue;
            }

            $c->update(['status' => Certificate::STATUS_GENERATING]);
            GenerateCertificatePdfJob::dispatch($c);
            $dispatched++;
        }

        $message = "{$dispatched} Sertifikat telah dimasukkan ke dalam antrean PDF.";
        if ($total > $limit) {
            $message .= " (Sisa " . ($total - $limit) . " data lainnya perlu diproses pada batch berikutnya demi keamanan server).";
        }

        return back()->with('success', $message);
    }

    public function preview(Certificate $certificate)
    {
        $pdfPath = $this->normalizePdfPath($certificate->signed_pdf_path ?: $certificate->pdf_path);

        if (!$pdfPath)
            return back()->with('error', 'PDF belum tersedia.');
        if (!Storage::disk('public')->exists($pdfPath)) {
            return back()->with('error', 'File PDF tidak ditemukan di storage/public.');
        }

        return response()->file(Storage::disk('public')->path($pdfPath), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="sertifikat.pdf"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    public function download(Certificate $certificate)
    {
        $pdfPath = $this->normalizePdfPath($certificate->signed_pdf_path ?: $certificate->pdf_path);

        if (!$pdfPath)
            return back()->with('error', 'PDF belum tersedia.');
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
            $token = (string)Str::uuid();
        } while (Certificate::where('verify_token', $token)->exists());

        return $token;
    }

    private function normalizePdfPath(?string $path): ?string
    {
        $path = trim((string)$path);
        if ($path === '')
            return null;

        $path = preg_replace('#^storage/#', '', $path);
        $path = preg_replace('#^public/#', '', $path);
        $path = ltrim($path, '/');

        return $path;
    }



}