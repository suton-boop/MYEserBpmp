<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Certificate;
use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->query('year', date('Y'));

        // Overall stats
        $totalEvents = Event::whereYear('start_date', $year)->orWhereYear('end_date', $year)->count();
        $totalParticipants = Participant::whereHas('event', function ($q) use ($year) {
            $q->whereYear('start_date', $year)->orWhereYear('end_date', $year);
        })->count();

        $totalDraft = Certificate::whereHas('event', function ($q) use ($year) {
            $q->whereYear('start_date', $year)->orWhereYear('end_date', $year);
        })->where('status', 'draft')->count();

        $totalSigned = Certificate::whereHas('event', function ($q) use ($year) {
            $q->whereYear('start_date', $year)->orWhereYear('end_date', $year);
        })->whereIn('status', ['signed', 'terbit', 'final_generated', 'sent'])->count();

        // Datatable
        $events = Event::withCount([
            'participants',
            'certificates',
            'certificates as cert_draft' => fn($q) => $q->where('status', 'draft'),
            'certificates as cert_submitted' => fn($q) => $q->where('status', 'submitted'),
            'certificates as cert_approved' => fn($q) => $q->where('status', 'approved'),
            'certificates as cert_signed' => fn($q) => $q->whereIn('status', ['signed', 'terbit', 'final_generated', 'sent']),
        ])
            ->whereYear('start_date', $year)
            ->orWhereYear('end_date', $year)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        // Distinct years for filter
        $years = Event::select(DB::raw('YEAR(start_date) as year'))
            ->whereNotNull('start_date')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        if (!in_array(date('Y'), $years)) {
            $years[] = date('Y');
            rsort($years);
        }

        return view('admin.reports.index', compact(
            'year', 'years', 'totalEvents', 'totalParticipants', 'totalDraft', 'totalSigned', 'events'
        ));
    }
}
