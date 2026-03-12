<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Certificate;
use App\Models\CertificateTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string)$request->query('q', ''));
        $status = $request->query('status');

        $events = Event::query()
            ->with(['certificateTemplate']) // tampilkan template di list (opsional)
            ->withCount('participants')
            ->when($q !== '', function ($query) use ($q) {
            $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('location', 'like', "%{$q}%");
                }
                );
            })
            ->when(in_array($status, ['draft', 'active', 'closed'], true), function ($query) use ($status) {
            $query->where('status', $status);
        })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.system.events.index', compact('events', 'q', 'status'));
    }

    public function create()
    {
        // hanya template aktif yang boleh dipilih
        $templates = CertificateTemplate::query()
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();

        return view('admin.system.events.create', compact('templates'));
    }

    public function store(Request $request)
    {
        // ensure certificate_template_id is null if empty
        if (!$request->filled('certificate_template_id')) {
            $request->merge(['certificate_template_id' => null]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'in:draft,active,closed'],
            'description' => ['required', 'string'],

            // pilih template
            'certificate_template_id' => ['nullable', 'integer', 'exists:certificate_templates,id'],

            // ✅ appendix halaman 2 (json string dari textarea)
            'certificate_appendix' => ['nullable', 'json'],
            'is_date_per_participant' => ['nullable', 'boolean'],
            'signing_date' => ['nullable', 'date'],
        ]);

        // jika template dipilih, pastikan template tersebut aktif
        if (!empty($data['certificate_template_id'])) {
            $isActive = CertificateTemplate::where('id', $data['certificate_template_id'])
                ->where('is_active', 1)
                ->exists();

            if (!$isActive) {
                return back()->withInput()->with('error', 'Template yang dipilih sedang nonaktif.');
            }
        }

        $data['is_date_per_participant'] = $request->boolean('is_date_per_participant');

        Event::create($data);

        return redirect()
            ->route('admin.system.events.index')
            ->with('success', 'Event berhasil ditambahkan.');
    }

    public function edit(Event $event)
    {
        // hanya template aktif yang boleh dipilih
        $templates = CertificateTemplate::query()
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();

        return view('admin.system.events.edit', compact('event', 'templates'));
    }

    public function update(Request $request, Event $event)
    {
        // ensure certificate_template_id is null if empty
        if (!$request->filled('certificate_template_id')) {
            $request->merge(['certificate_template_id' => null]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'in:draft,active,closed'],
            'description' => ['required', 'string'],

            'certificate_template_id' => ['nullable', 'integer', 'exists:certificate_templates,id'],

            // ✅ appendix halaman 2
            'certificate_appendix' => ['nullable', 'json'],
            'is_date_per_participant' => ['nullable', 'boolean'],
            'signing_date' => ['nullable', 'date'],
        ]);

        if (!empty($data['certificate_template_id'])) {
            $isActive = CertificateTemplate::where('id', $data['certificate_template_id'])
                ->where('is_active', 1)
                ->exists();

            if (!$isActive) {
                return back()->withInput()->with('error', 'Template yang dipilih sedang nonaktif.');
            }
        }

        $data['is_date_per_participant'] = $request->boolean('is_date_per_participant');

        $event->update($data);

        return redirect()
            ->route('admin.system.events.index')
            ->with('success', 'Event berhasil diperbarui.');
    }

    public function destroy(Event $event)
    {
        $event->delete();

        return redirect()
            ->route('admin.system.events.index')
            ->with('success', 'Event berhasil dihapus.');
    }

    public function downloadSigned(Event $event)
    {
        // Ambil sertifikat yang statusnya 'signed'
        $certificates = $event->certificates()
            ->with('participant')
            ->where('status', Certificate::STATUS_SIGNED)
            ->whereNotNull('signed_pdf_path')
            ->get();

        if ($certificates->isEmpty()) {
            return back()->with('error', 'Tidak ada sertifikat yang sudah ditanda tangani (TTE) untuk event ini.');
        }

        $zip = new ZipArchive();
        $fileName = 'sertifikat-' . Str::slug((string)$event->name) . '-' . now()->format('YmdHis') . '.zip';
        $zipPath = storage_path('app/public/tmp/' . $fileName);

        // Pastikan direktori tmp ada
        if (!is_dir(storage_path('app/public/tmp'))) {
            @mkdir(storage_path('app/public/tmp'), 0777, true);
        }

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($certificates as $cert) {
                if ($cert->signed_pdf_path && Storage::disk('public')->exists($cert->signed_pdf_path)) {
                    $pdfPath = Storage::disk('public')->path($cert->signed_pdf_path);

                    // Gunakan nama file yang deskriptif di dalam zip
                    $participantName = Str::slug((string)($cert->participant->name ?? $cert->id));
                    $insideName = "sertifikat-{$participantName}-{$cert->id}.pdf";

                    $zip->addFile($pdfPath, $insideName);
                }
            }
            $zip->close();
        }
        else {
            return back()->with('error', 'Gagal membuat file ZIP.');
        }

        if (!file_exists($zipPath)) {
            return back()->with('error', 'File ZIP tidak berhasil dibuat.');
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }
}