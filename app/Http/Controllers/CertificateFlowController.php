<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\Http\Request;

class CertificateFlowController extends Controller
{
    public function submit(Certificate $certificate)
    {
        if (!in_array($certificate->status, [Certificate::STATUS_DRAFT, Certificate::STATUS_REJECTED])) {
            return back()->with('error', 'Hanya status DRAFT atau REJECTED yang bisa diajukan.');
        }

        $certificate->update([
            'status' => Certificate::STATUS_SUBMITTED,
            'submitted_at' => now(),
            'submitted_by' => auth()->id(),
            // Kosongkan alasan reject jika sedang diajukan ulang
            'rejected_at' => null,
            'rejected_by' => null,
            'rejected_note' => null,
        ]);

        return back()->with('success', 'Berhasil diajukan untuk persetujuan.');
    }

    public function submitAll(Request $request)
    {
        $data = $request->validate([
            'event_id' => ['required', 'integer', 'exists:events,id'],
        ]);

        $eventId = (int)$data['event_id'];

        $affected = Certificate::query()
            ->where('event_id', $eventId)
            ->whereIn('status', [Certificate::STATUS_DRAFT, Certificate::STATUS_REJECTED])
            ->update([
            'status' => Certificate::STATUS_SUBMITTED,
            'submitted_at' => now(),
            'rejected_at' => null,
            'rejected_by' => null,
            'rejected_note' => null,
        ]);

        if ($affected === 0) {
            return back()->with('error', 'Tidak ada sertifikat DRAFT untuk diajukan pada event ini.');
        }

        return back()->with('success', "Berhasil mengajukan {$affected} draft ke persetujuan.");
    }

    public function revise(Certificate $certificate)
    {
        // Izinkan revisi jika sudah TTE, sudah PDF final, Gagal TTE, atau sedang Terjadwal
        $allowed = [
            Certificate::STATUS_SIGNED, 
            Certificate::STATUS_FINAL_GENERATED, 
            Certificate::STATUS_SCHEDULED,
            'gagal_tte'
        ];

        if (!in_array(strtolower($certificate->status), $allowed)) {
            return back()->with('error', 'Hanya sertifikat yang sudah TTE, PDF Final, Scheduled, atau Gagal TTE yang bisa direvisi.');
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