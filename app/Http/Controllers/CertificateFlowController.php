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

    public function submitAll(Request $request)    {
        $data = $request->validate([
            'event_id' => ['required', 'integer', 'exists:events,id'],
        ]);

        $eventId = (int)$data['event_id'];

        $affected = Certificate::query()
            ->where('event_id', $eventId)
            ->where('status', Certificate::STATUS_DRAFT)
            ->update([
            'status' => Certificate::STATUS_SUBMITTED,
            'submitted_at' => now(),
            // HAPUS submitted_by karena kolomnya tidak ada
        ]);

        if ($affected === 0) {
            return back()->with('error', 'Tidak ada sertifikat DRAFT untuk diajukan pada event ini.');
        }

        return back()->with('success', "Berhasil mengajukan {$affected} draft ke persetujuan.");    }

    public function revise(Certificate $certificate)
    {
        // Izinkan revisi hanya jika sudah ditandatangani atau sudah generate PDF final
        if (!in_array($certificate->status, [Certificate::STATUS_SIGNED, Certificate::STATUS_FINAL_GENERATED])) {
            return back()->with('error', 'Hanya sertifikat yang sudah TTE atau PDF Final yang bisa direvisi.');
        }

        // Hapus file fisik jika ada
        if ($certificate->pdf_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($certificate->pdf_path);
        }
        if ($certificate->signed_pdf_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($certificate->signed_pdf_path);
        }

        // Reset status kembali ke APPROVED dengan NOMOR TETAP
        $certificate->update([
            'status' => Certificate::STATUS_APPROVED,
            'pdf_path' => null,
            'signed_pdf_path' => null,
            'signed_at' => null,
            'generated_at' => null,
        ]);

        return back()->with('success', 'Sertifikat berhasil di-reset ke status Approved. Silakan perbaiki data peserta lalu generate PDF ulang.');
    }
}