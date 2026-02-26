<?php

namespace App\Http\Controllers\Admin\Tte;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Event;
use App\Models\SignerCertificate;
use Illuminate\Http\Request;

class SigningController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

public function index(Request $request)
{
    $q = trim((string) $request->get('q', ''));
    $eventId = $request->filled('event_id') ? (int) $request->get('event_id') : null;

    $events = Event::query()->orderBy('name')->get(['id','name']);

    $signers = SignerCertificate::query()
        ->where('is_active', true)
        ->orderBy('name')
        ->get(['id','name','code']);

    $certificates = Certificate::query()
        ->with(['event', 'participant'])
        ->whereIn('status', ['approved', 'final_generated']) // ✅ penting, sesuai data kamu
        ->when($eventId !== null, fn($qr) => $qr->where('event_id', $eventId))
        ->when($q !== '', function ($qr) use ($q) {           // ✅ hanya kalau q benar-benar diisi
            $qr->where(function ($w) use ($q) {
                $w->where('certificate_number', 'like', "%{$q}%")
                  ->orWhere('certificate_no', 'like', "%{$q}%")
                  ->orWhereHas('participant', fn($p) => $p->where('name', 'like', "%{$q}%"));
            });
        })
        ->latest('id')
        ->paginate(20)
        ->withQueryString();

    return view('admin.tte.signing.index', compact('events','eventId','q','signers','certificates'));
}

    // dispatchBulk & dispatchSingle bisa kamu isi sesuai yang sudah kita bahas sebelumnya
}