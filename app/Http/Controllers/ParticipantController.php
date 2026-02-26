<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ParticipantController extends Controller
{
    /**
     * LIST + FILTER (Status mengikuti sertifikat TERAKHIR)
     */
    public function index(Request $request)
    {
        $q       = trim((string) $request->query('q', ''));
        $eventId = $request->query('event_id');
        $status  = $request->query('status');

        $perPage = (int) $request->query('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;

        $events = Event::orderBy('name')->get(['id', 'name']);

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
                ' . (!empty($eventId) && is_numeric($eventId) ? 'AND c2.event_id = ' . (int) $eventId : '') . '
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
            ->when(!empty($eventId) && is_numeric($eventId), fn ($qq) => $qq->where('participants.event_id', (int) $eventId))
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($sub) use ($q) {
                    $sub->where('participants.name', 'like', "%{$q}%")
                        ->orWhere('participants.email', 'like', "%{$q}%")
                        ->orWhere('participants.nik', 'like', "%{$q}%")
                        ->orWhere('participants.institution', 'like', "%{$q}%");
                });
            })
            // ✅ filter status ikut status sertifikat terakhir
            ->when($status !== null && $status !== '', function ($qq) use ($status) {
                // kalau mau "draft" dianggap peserta tanpa sertifikat juga:
                if ($status === 'draft') {
                    $qq->where(function($w){
                        $w->whereNull('lc.cert_status')
                          ->orWhere('lc.cert_status', 'draft');
                    });
                } else {
                    $qq->where('lc.cert_status', $status);
                }
            })
            ->orderByDesc('participants.id')
            ->paginate($perPage)
            ->withQueryString();
            return view('participants.index', compact(
            'participants','events','q','eventId','status','perPage'
            ));

        
    }

    // ======================
    // METHOD LAIN BIARKAN PUNYA KAMU (templateCsv, importForm, importStore, create, store, edit, update, destroy)
    // ======================

    public function templateCsv(): StreamedResponse
    {
        $filename = 'template_peserta.csv';

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, ['name', 'email', 'nik', 'institution', 'status']);
            fputcsv($out, ['Budi Santoso', 'budi@email.com', '6401xxxxxxxxxxxx', 'SMPN 1 Samarinda', 'draft']);
            fputcsv($out, ['Siti Aminah', 'siti@email.com', '6401xxxxxxxxxxxx', 'BPMP Kaltim', 'terbit']);
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    // ... lanjutkan method lain milik kamu apa adanya
}