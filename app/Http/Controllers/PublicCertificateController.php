<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PublicCertificateController extends Controller
{
    public function search(Request $request)
    {
        $keyword = trim((string)$request->input('keyword'));

        if (empty($keyword)) {
            return back()->with('error', 'Masukkan kata kunci pencarian.');
        }

        $results = \App\Models\Certificate::with(['participant', 'event'])
            ->whereIn('status', [\App\Models\Certificate::STATUS_SIGNED, 'terbit', \App\Models\Certificate::STATUS_FINAL_GENERATED, \App\Models\Certificate::STATUS_SENT])
            ->whereHas('participant', function ($q) use ($keyword) {
            $q->where('name', 'like', "%{$keyword}%")
                ->orWhere('nik', 'like', "%{$keyword}%")
                ->orWhere('email', 'like', "%{$keyword}%")
                ->orWhere('institution', 'like', "%{$keyword}%");
        })
            ->latest()
            ->get();

        return view('public.search', compact('results', 'keyword'));
    }

    public function download($code)
    {
        $cert = \App\Models\Certificate::where('verify_token', $code)->firstOrFail();

        $pdfPath = $cert->signed_pdf_path ?: $cert->pdf_path;

        if (!$pdfPath || !\Illuminate\Support\Facades\Storage::disk('public')->exists($pdfPath)) {
            abort(404, 'Sertifikat PDF belum tersedia.');
        }

        // Catat unduhan
        $cert->increment('download_count');
        $cert->update(['last_downloaded_at' => now()]);

        $filename = 'sertifikat-' . ($cert->participant->name ?? $cert->certificate_number) . '.pdf';
        $filename = preg_replace('/[^A-Za-z0-9\-\_\.]/', '-', $filename);

        return \Illuminate\Support\Facades\Storage::disk('public')->download($pdfPath, $filename);
    }

    public function verifyForm()
    {
        return view('public.verify-form');
    }

    public function verifyByNumber(Request $request)
    {
        $certNo = trim((string)$request->input('certificate_number'));

        if (empty($certNo)) {
            return back()->with('error', 'Masukkan nomor sertifikat.');
        }

        $cert = \App\Models\Certificate::with(['participant', 'event'])
            ->where('certificate_number', $certNo)
            ->whereIn('status', [\App\Models\Certificate::STATUS_SIGNED, 'terbit', \App\Models\Certificate::STATUS_FINAL_GENERATED, \App\Models\Certificate::STATUS_SENT])
            ->first();

        // Pass status = valid or invalid ke view
        if ($cert) {
            return redirect()->route('public.verify.show', $cert->verify_token);
        }
        else {
            return view('public.verify-show', ['cert' => null, 'certNo' => $certNo, 'isValid' => false]);
        }
    }

    public function verifyByToken($code)
    {
        $cert = \App\Models\Certificate::with(['participant', 'event', 'digitalSignature.signerCertificate'])
            ->where('verify_token', $code)
            ->whereIn('status', [\App\Models\Certificate::STATUS_SIGNED, 'terbit', \App\Models\Certificate::STATUS_FINAL_GENERATED, \App\Models\Certificate::STATUS_SENT])
            ->first();

        if ($cert) {
            return view('public.verify-show', ['cert' => $cert, 'certNo' => $cert->certificate_number, 'isValid' => true]);
        }
        else {
            return view('public.verify-show', ['cert' => null, 'certNo' => '-', 'isValid' => false]);
        }
    }
}