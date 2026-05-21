<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Certificate;
use App\Models\Event;

class EmailController extends Controller
{
    public function index(Request $request)
    {
        $query = Certificate::with(['participant', 'event'])
            ->whereIn('status', [Certificate::STATUS_SIGNED, 'terbit', Certificate::STATUS_FINAL_GENERATED, Certificate::STATUS_SENT]);

        // Filter event search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('participant', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $certificates = $query->orderBy('created_at', 'desc')->paginate(15);
        $events = Event::orderBy('name')->get();

        return view('emails.index', compact('certificates', 'events'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'certificate_ids' => 'required|array',
            'certificate_ids.*' => 'exists:certificates,id',
        ]);

        $ids = $request->certificate_ids;

        // Batasi maksimal 200 email per klik
        $toProcess = array_slice($ids, 0, 200);
        
        $certificates = Certificate::with('participant')->whereIn('id', $toProcess)->get();

        $dispatched = 0;
        $invalidEmails = 0;

        foreach ($certificates as $cert) {
            $email = $cert->participant->email;
            if (!$email) continue;
            
            // Cek validitas format email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $invalidEmails++;
                continue;
            }
            
            \App\Jobs\SendCertificateEmailJob::dispatch($cert);
            $dispatched++;
        }

        $msg = $dispatched . ' Sertifikat masuk antrean pengiriman.';
        if ($invalidEmails > 0) {
            $msg .= ' (' . $invalidEmails . ' email dilewati karena format tidak valid).';
        }
        if (count($ids) > 200) {
            $msg .= ' (Sisa ' . (count($ids) - 200) . ' data diproses pada batch berikutnya).';
        }

        return back()->with('success', $msg);
    }
}
