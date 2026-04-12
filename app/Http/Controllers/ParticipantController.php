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
        $events = Event::where('status', Event::STATUS_ACTIVE)->orderBy('name')->get(['id', 'name']);
        return view('participants.import', compact('events'));
    }

    public function importStore(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'file' => 'required|file|mimes:xls,xlsx,csv,txt|max:4096'
        ]);

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

            // Proses ekstra kolom sebagai metadata (nilai, nilai praktek, dsb)
            $metadata = [];
            foreach ($extraHeaders as $index => $colName) {
                $val = trim($row[$index + 5] ?? '');
                $cleanColName = strtolower(trim($colName ?? ''));
                if ($cleanColName !== '') {
                    $metadata[$cleanColName] = $val;
                    
                    // Deteksi tanggal (jika header mengandung kata 'tanggal')
                    if (str_contains($cleanColName, 'tanggal')) {
                        $metadata['detected_date'] = $val;
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

        return redirect()->route('admin.participants.index', ['event_id' => $request->event_id])
            ->with('success', "Import $inserted peserta dari Excel/CSV berhasil diselesaikan.");
    }

    public function create()
    {
        $events = Event::where('status', Event::STATUS_ACTIVE)->orderBy('name')->get(['id', 'name']);
        $eventId = request('event_id');
        return view('participants.create', compact('events', 'eventId'));
    }

    public function store(Request $request)
    {
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

        Participant::create($validated);

        return redirect()->route('admin.participants.index', ['event_id' => $request->event_id])
            ->with('success', 'Peserta baru berhasil ditambahkan.');
    }

    public function edit(Participant $participant)
    {
        $events = Event::orderBy('name')->get(['id', 'name']);
        return view('participants.edit', compact('participant', 'events'));
    }

    public function update(Request $request, Participant $participant)
    {
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