<?php

namespace App\Http\Controllers\Tte;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tte\CertificateCreateRequest;
use App\Models\Certificate;
use App\Domain\Certificates\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CertificateController extends Controller
{
    public function __construct(private AuditLogger $audit)
    {
        $this->middleware(['auth']);
    }

    public function store(CertificateCreateRequest $request)
    {
        $cert = Certificate::query()->create([
            'id' => (string) Str::uuid(),
            'certificate_no' => $request->string('certificate_no'),
            'title' => $request->string('title'),
            'owner_name' => $request->string('owner_name')->toString(),
            'owner_identifier' => $request->string('owner_identifier')->toString(),
            'status' => 'draft',
            'approval_level_required' => (int) $request->input('approval_level_required'),
            'approval_level_current' => 0,
            'created_by' => $request->user()->id,
        ]);

        $this->audit->log('certificate.created', $cert->id, Certificate::class, [
            'certificate_no' => $cert->certificate_no,
        ], $request->user()->id, $request->ip(), $request->userAgent());

        return response()->json(['data' => $cert], 201);
    }

    // Upload/generated PDF (mis. dari engine sertifikat Anda)
    public function uploadPdf(Request $request, string $id)
    {
        $request->validate([
            'pdf' => ['required','file','mimes:pdf','max:10240'],
        ]);

        $cert = Certificate::query()->findOrFail($id);

        // Policy bisa ditambah untuk memastikan hanya creator/operator tertentu
        if ($cert->status !== 'draft' && $cert->status !== 'generated') {
            abort(422, 'Invalid status to upload PDF.');
        }

        $path = $request->file('pdf')->store(config('tte.pdf.pdf_root').'/generated', config('tte.pdf.storage_disk'));

        $cert->update([
            'pdf_path' => $path,
            'pdf_checksum' => hash_file('sha256', Storage::disk(config('tte.pdf.storage_disk'))->path($path)),
            'status' => 'generated',
            'generated_at' => now(),
        ]);

        $this->audit->log('certificate.pdf_uploaded', $cert->id, Certificate::class, [
            'pdf_path' => $path,
            'checksum' => $cert->pdf_checksum,
        ], $request->user()->id, $request->ip(), $request->userAgent());

        return response()->json(['data' => $cert]);
    }
}