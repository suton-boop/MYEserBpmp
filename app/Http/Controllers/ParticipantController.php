<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ParticipantsTemplateExport;

class ParticipantController extends Controller
{
    /**
     * LIST + FILTER (Status mengikuti sertifikat TERAKHIR)
     */
    public function index(Request $request)
    {
        $q = trim((string)$request->query('q', ''));
        $eventId = $request->query('event_id');
        $status = $request->query('status');

        $perPage = (int)$request->query('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;

        $sortBy = $request->query('sort', 'latest');

        $events = Event::whereIn('status', [Event::STATUS_ACTIVE, Event::STATUS_CLOSED])->orderBy('name')->get(['id', 'name']);

        // Subquery: ambil 1 sertifikat terakhir per peserta
        // kalau event dipilih => sertifikat terakhir untuk event itu
        $latestCertSub = DB::table('certificates as c1')
            ->select(
            'c1.participant_id',
            'c1.status as cert_status',
            'c1.certificate_number',
            'c1.updated_at'
        )
            ->whereRaw('c1.id = (
                SELECT c2.id FROM certificates c2
                WHERE c2.participant_id = c1.participant_id
                ' . (!empty($eventId) && is_numeric($eventId) ? 'AND c2.event_id = ' . (int)$eventId : '') . '
                ORDER BY c2.updated_at DESC, c2.id DESC
                LIMIT 1
            )');

        $participants = Participant::query()
            ->select(
            'participants.*',
            'lc.cert_status',
            'lc.certificate_number as last_certificate_number'
        )
            ->leftJoinSub($latestCertSub, 'lc', function ($join) {
            $join->on('lc.participant_id', '=', 'participants.id');
        })
            ->with(['event:id,name'])
            ->when(!empty($eventId) && is_numeric($eventId), fn($qq) => $qq->where('participants.event_id', (int)$eventId))
            ->when($q !== '', function ($qq) use ($q) {
            $qq->where(function ($sub) use ($q) {
                    $sub->where('participants.name', 'like', "%{$q}%")
                        ->orWhere('participants.email', 'like', "%{$q}%")
                        ->orWhere('participants.nik', 'like', "%{$q}%")
                        ->orWhere('participants.institution', 'like', "%{$q}%");
                }
                );
            })
            // ✅ filter status ikut status sertifikat terakhir
            ->when($status !== null && $status !== '', function ($qq) use ($status) {
            // kalau mau "draft" dianggap peserta tanpa sertifikat juga:
            if ($status === 'draft') {
                $qq->where(function ($w) {
                            $w->whereNull('lc.cert_status')
                                ->orWhere('lc.cert_status', 'draft');
                        }
                        );
                    }
                    else {
                        $qq->where('lc.cert_status', $status);
                    }
                })
            ->when($sortBy === 'name_asc', fn($qq) => $qq->orderBy('participants.name', 'asc'))
            ->when($sortBy === 'name_desc', fn($qq) => $qq->orderBy('participants.name', 'desc'))
            ->when($sortBy === 'oldest', fn($qq) => $qq->orderBy('participants.id', 'asc'))
            ->when($sortBy === 'latest' || !$sortBy, fn($qq) => $qq->orderByDesc('participants.id'))
            ->paginate($perPage)
            ->withQueryString();
        return view('participants.index', compact(
            'participants', 'events', 'q', 'eventId', 'status', 'perPage', 'sortBy'
        ));


    }

    // ======================
    // MENGEMBALIKAN METHOD RESOURCE YANG HILANG
    // ======================

    public function importForm()
    {
        $query = Event::query();
        if (!auth()->user()->isFullAdmin()) {
            $query->where('status', Event::STATUS_ACTIVE);
        }
        $events = $query->orderBy('name')->get(['id', 'name']);
        return view('participants.import', compact('events'));
    }

    public function importStore(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'file' => 'required|file|mimes:xls,xlsx,csv,txt|max:4096'
        ]);

        $event = Event::findOrFail($request->event_id);
        if ($event->status === Event::STATUS_CLOSED && !auth()->user()->isFullAdmin()) {
            return back()->with('error', 'Event ini sudah selesai/ditutup. Peserta baru tidak bisa ditambahkan oleh Operator/Ketua GM.');
        }

        $rows = Excel::toArray(new \stdClass, $request->file('file'));

        if (empty($rows) || empty($rows[0])) {
            return back()->with('error', 'File Excel/CSV kosong atau tidak bisa dibaca.');
        }

        $data = $rows[0];
        $header = array_shift($data); // Ambil baris pertama sebagai Header

        if (!$header || count($header) < 5) {
            return back()->with('error', 'Format/Header Excel tidak valid. Pastikan mengikuti template terbaru.');
        }

        // Simpan sisa kolom untuk metadata
        // index 5, 6, 7 dst tergantung isi header
        $extraHeaders = array_slice($header, 5);

        $inserted = 0;
        $skipped = 0;

        foreach ($data as $row) {
            // Kolom wajib dasar: name, email, nik, institution, status
            if (count($row) < 5)
                continue;

            $name = trim($row[0] ?? '');
            $email = trim($row[1] ?? '');
            $nik = trim($row[2] ?? '');
            $institution = trim($row[3] ?? '');
            $status = strtolower(trim($row[4] ?? '')) === 'terbit' ? 'terbit' : 'draft';

            if (!$name)
                continue;

            // ✅ CEK DUPLIKAT (Cerdas: NIK Utama, atau Nama+Email jika NIK kosong)
            $allowDuplicate = \App\Models\Setting::getValue('allow_duplicate_participants', false);
            
            if (!$allowDuplicate) {
                $isDuplicate = Participant::where('event_id', $request->event_id)
                    ->where(function ($q) use ($nik, $email, $name) {
                        if ($nik) {
                            // Jika ada NIK, maka NIK harus unik di event tersebut
                            $q->where('nik', $nik);
                        } elseif ($email && $name) {
                            // Jika NIK kosong, maka kombinasi Nama + Email harus unik
                            $q->where('name', $name)->where('email', $email);
                        }
                    })
                    ->exists();

                if ($isDuplicate) {
                    $skipped++;
                    continue;
                }
            }

            // Proses ekstra kolom sebagai metadata (nilai, nilai praktek, dsb)
            $metadata = [];
            foreach ($extraHeaders as $index => $colName) {
                $val = trim($row[$index + 5] ?? '');
                $cleanColName = strtolower(trim($colName ?? ''));
                if ($cleanColName !== '') {
                    $metadata[$cleanColName] = $val;
                    
                    // Deteksi tanggal (jika header mengandung kata 'tanggal')
                    if (str_contains($cleanColName, 'tanggal')) {
                        $parsedDate = $val;
                        if (is_numeric($val)) {
                            try {
                                $parsedDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($val)->format('Y-m-d');
                            } catch (\Exception $e) {}
                        } elseif (!empty($val)) {
                            try {
                                $idMonths = ['januari' => 'january', 'februari' => 'february', 'maret' => 'march', 'mei' => 'may', 'juni' => 'june', 'juli' => 'july', 'agustus' => 'august', 'oktober' => 'october', 'nopember' => 'november', 'desember' => 'december'];
                                $valEn = str_ireplace(array_keys($idMonths), array_values($idMonths), $val);
                                
                                // Coba format DD/MM/YYYY jika string mengandung '/'
                                if (str_contains($valEn, '/')) {
                                    try {
                                        $parsedDate = \Carbon\Carbon::createFromFormat('d/m/Y', trim($valEn))->format('Y-m-d');
                                    } catch (\Exception $e) {
                                        $parsedDate = \Carbon\Carbon::parse($valEn)->format('Y-m-d');
                                    }
                                } else {
                                    $parsedDate = \Carbon\Carbon::parse($valEn)->format('Y-m-d');
                                }
                            } catch (\Exception $e) {}
                        }
                        $metadata['detected_date'] = $parsedDate;
                        $metadata[$cleanColName] = $parsedDate; // Update nilai metadata dengan format Y-m-d
                    }
                }
            }

            Participant::create([
                'event_id' => $request->event_id,
                'custom_date' => $metadata['detected_date'] ?? null,
                'name' => $name,
                'email' => $email ?: null,
                'nik' => $nik ?: null,
                'institution' => $institution ?: null,
                'daerah' => $metadata['daerah'] ?? null,
                'jenjang' => $metadata['jenjang'] ?? null,
                'peran' => $metadata['peran'] ?? null,
                'keterangan' => $metadata['keterangan'] ?? null,
                'status' => $status,
                'metadata' => empty($metadata) ? null : $metadata,
            ]);

            $inserted++;
        }

        $msg = "Import $inserted peserta berhasil diselesaikan.";
        if ($skipped > 0) {
            $msg .= " ($skipped data duplikat dilewati).";
        }

        return redirect()->route('admin.participants.index', ['event_id' => $request->event_id])
            ->with('success', $msg);
    }

    public function create()
    {
        $query = Event::query();
        if (!auth()->user()->isFullAdmin()) {
            $query->where('status', Event::STATUS_ACTIVE);
        }
        $events = $query->orderBy('name')->get(['id', 'name']);
        $eventId = request('event_id');
        return view('participants.create', compact('events', 'eventId'));
    }

    public function store(Request $request)
    {
        $event = Event::findOrFail($request->event_id);
        if ($event->status === Event::STATUS_CLOSED && !auth()->user()->isFullAdmin()) {
            return back()->with('error', 'Event ini sudah selesai/ditutup. Peserta baru tidak bisa ditambahkan oleh Operator/Ketua GM.');
        }

        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'status' => 'required|in:draft,terbit',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'nik' => 'nullable|string|max:50',
            'institution' => 'required|string|max:255',
            'daerah' => 'required|string|max:255',
            'jenjang' => 'required|string|max:255',
            'peran' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
            'metadata' => 'nullable|string',
            'custom_date' => 'nullable|date',
        ]);

        if (!empty($validated['metadata'])) {
            $validated['metadata'] = json_decode($validated['metadata'], true);
        }
        else {
            $validated['metadata'] = null;
        }

        // ✅ CEK DUPLIKAT MANUAL (Cerdas: NIK Utama, atau Nama+Email jika NIK kosong)
        $allowDuplicate = \App\Models\Setting::getValue('allow_duplicate_participants', false);
        
        if (!$allowDuplicate) {
            $isDuplicate = Participant::where('event_id', $validated['event_id'])
                ->where(function ($q) use ($validated) {
                    if (!empty($validated['nik'])) {
                        $q->where('nik', $validated['nik']);
                    }
                    elseif (!empty($validated['email']) && !empty($validated['name'])) {
                        $q->where('name', $validated['name'])
                            ->where('email', $validated['email']);
                    }
                })
                ->exists();

            if ($isDuplicate) {
                return back()->withInput()->with('error', 'Peserta dengan NIK atau Email tersebut sudah terdaftar di event ini.');
            }
        }

        Participant::create($validated);

        return redirect()->route('admin.participants.index', ['event_id' => $request->event_id])
            ->with('success', 'Peserta baru berhasil ditambahkan.');
    }

    public function edit(Participant $participant)
    {
        // Cek apakah sertifikat sudah TTE (Signed/Sent)
        $hasSignedCert = $participant->certificates()->whereIn('status', ['signed', 'sent'])->exists();
        if ($hasSignedCert && !auth()->user()->isFullAdmin()) {
            return back()->with('error', 'Peserta ini sudah memiliki sertifikat bertanda tangan (TTE). Perubahan hanya bisa dilakukan oleh Admin atau Super Admin.');
        }

        // Cek apakah event sudah selesai
        if ($participant->event->status === \App\Models\Event::STATUS_CLOSED && !auth()->user()->isFullAdmin()) {
            return back()->with('error', 'Event ini sudah selesai/ditutup. Data peserta tidak bisa diubah oleh Operator/Ketua GM.');
        }

        $events = Event::orderBy('name')->get(['id', 'name']);
        return view('participants.edit', compact('participant', 'events'));
    }

    public function update(Request $request, Participant $participant)
    {
        // Cek apakah sertifikat sudah TTE (Signed/Sent)
        $hasSignedCert = $participant->certificates()->whereIn('status', ['signed', 'sent'])->exists();
        if ($hasSignedCert && !auth()->user()->isFullAdmin()) {
            return back()->with('error', 'Peserta ini sudah memiliki sertifikat bertanda tangan (TTE). Perubahan hanya bisa dilakukan oleh Admin atau Super Admin.');
        }

        // Cek apakah event sudah selesai
        if ($participant->event->status === Event::STATUS_CLOSED && !auth()->user()->isFullAdmin()) {
            return back()->with('error', 'Event ini sudah selesai/ditutup. Data peserta tidak bisa diubah oleh Operator/Ketua GM.');
        }

        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'status' => 'required|in:draft,terbit',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'nik' => 'nullable|string|max:50',
            'institution' => 'required|string|max:255',
            'daerah' => 'required|string|max:255',
            'jenjang' => 'required|string|max:255',
            'peran' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
            'metadata' => 'nullable|string',
            'custom_date' => 'nullable|date',
        ]);

        if (!empty($validated['metadata'])) {
            $validated['metadata'] = json_decode($validated['metadata'], true);
        }
        else {
            $validated['metadata'] = null;
        }

        $participant->update($validated);

        return redirect()->route('admin.participants.index', ['event_id' => $request->event_id])
            ->with('success', 'Data peserta berhasil diperbarui.');
    }

    public function destroy(Participant $participant)
    {
        // Cek apakah sertifikat sudah TTE (Signed/Sent)
        $hasSignedCert = $participant->certificates()->whereIn('status', ['signed', 'sent'])->exists();
        if ($hasSignedCert && !auth()->user()->isFullAdmin()) {
            return back()->with('error', 'Peserta ini sudah memiliki sertifikat bertanda tangan (TTE). Penghapusan hanya bisa dilakukan oleh Admin atau Super Admin.');
        }

        // Cek apakah event sudah selesai
        if ($participant->event->status === Event::STATUS_CLOSED && !auth()->user()->isFullAdmin()) {
            return back()->with('error', 'Event ini sudah selesai/ditutup. Data peserta tidak bisa dihapus oleh Operator/Ketua GM.');
        }

        $participant->delete();
        return back()->with('success', 'Data peserta berhasil dihapus.');
    }

    public function templateExcel()
    {
        return Excel::download(new ParticipantsTemplateExport, 'template_peserta.xlsx');
    }

    /**
     * DETEKSI DUPLIKAT DATA (Per Event)
     */
    public function duplicates(Request $request)
    {
        $type = $request->query('type', 'nik'); // default cek NIK
        $eventId = $request->query('event_id');

        if (!in_array($type, ['nik', 'email', 'name'])) {
            $type = 'nik';
        }

        // 1. Cari pasangan (nilai, event_id) yang muncul > 1 kali
        $duplicateGroups = DB::table('participants')
            ->select($type, 'event_id', DB::raw('count(*) as total'))
            ->whereNotNull($type)
            ->where($type, '!=', '')
            ->when(!empty($eventId), fn($q) => $q->where('event_id', $eventId))
            ->groupBy($type, 'event_id')
            ->having('total', '>', 1);

        // 2. Ambil data lengkap peserta yang masuk dalam kelompok duplikat tersebut
        $participants = Participant::query()
            ->with(['event:id,name'])
            ->joinSub($duplicateGroups, 'dups', function ($join) use ($type) {
            $join->on('participants.' . $type, '=', 'dups.' . $type)
                ->on('participants.event_id', '=', 'dups.event_id');
        })
            ->select('participants.*')
            ->orderBy('participants.event_id')
            ->orderBy("participants.$type")
            ->get();

        $events = Event::orderBy('name')->get(['id', 'name']);

        return view('participants.duplicates', compact('participants', 'type', 'events', 'eventId'));
    }
}