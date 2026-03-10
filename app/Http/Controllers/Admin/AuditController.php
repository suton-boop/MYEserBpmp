<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\\Models\\AuditLog;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $eventType = $request->query('event_type');
        $actorId = $request->query('actor_id');

        $logs = AuditLog::with('actor')
            ->when($eventType, fn($q) => $q->where('event_type', $eventType))
            ->when($actorId, fn($q) => $q->where('actor_id', $actorId))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.audit.index', compact('logs', 'eventType', 'actorId'));
    }
}
