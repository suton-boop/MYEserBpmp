<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\Http\Request;

class CertificateFlowController extends Controller
{
    public function submit(Certificate $certificate)
    {
        if ($certificate->status !== Certificate::STATUS_DRAFT) {
            return back()->with('error', 'Hanya status DRAFT yang bisa diajukan.');
        }

        $certificate
            ->update([
        'status' => Certificate::STATUS_SUBMITTED,
        'submitted_at' => now(),
        'submitted_by' => auth()->id(),
        ]);
    

        return back()->with('success', 'Berhasil diajukan untuk persetujuan.');
    }

   public function submitAll(Request $request)
{
    $data = $request->validate([
        'event_id' => ['required', 'integer', 'exists:events,id'],
    ]);

    $eventId = (int) $data['event_id'];

    $affected = Certificate::query()
        ->where('event_id', $eventId)
        ->where('status', Certificate::STATUS_DRAFT)
        ->update([
            'status'       => Certificate::STATUS_SUBMITTED,
            'submitted_at' => now(),
            // HAPUS submitted_by karena kolomnya tidak ada
        ]);

    if ($affected === 0) {
        return back()->with('error', 'Tidak ada sertifikat DRAFT untuk diajukan pada event ini.');
    }

    return back()->with('success', "Berhasil mengajukan {$affected} draft ke persetujuan.");
}
}