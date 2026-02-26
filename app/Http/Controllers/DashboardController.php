<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Participant;
use App\Models\Certificate;

class DashboardController extends Controller
{
    public function index()
    {
        $totalEvents       = Event::count();
        $totalParticipants = Participant::count();
        $totalCertificates = Certificate::count();

        $approved  = Certificate::where('status','approved')->count();
        $signed    = Certificate::where('status','signed')->count();
        $pending   = Certificate::where('status','pending')->count();
        $rejected  = Certificate::where('status','rejected')->count();

        $certPerEvent = Certificate::selectRaw('event_id, COUNT(*) as total')
            ->groupBy('event_id')
            ->with('event:id,name')
            ->get();

        return view('dashboard', compact(
            'totalEvents',
            'totalParticipants',
            'totalCertificates',
            'approved',
            'signed',
            'pending',
            'rejected',
            'certPerEvent'
        ));
    }
}