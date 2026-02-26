<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\CertificateTemplate;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $status = $request->query('status');

        $events = Event::query()
            ->with(['certificateTemplate'])      // tampilkan template di list (opsional)
            ->withCount('participants')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('location', 'like', "%{$q}%");
                });
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
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'location'    => ['nullable', 'string', 'max:255'],
            'start_date'  => ['required', 'date'],
            'end_date'    => ['nullable', 'date', 'after_or_equal:start_date'],
            'status'      => ['required', 'in:draft,active,closed'],
            'description' => ['nullable', 'string'],

            // pilih template
            'certificate_template_id' => ['nullable', 'integer', 'exists:certificate_templates,id'],

            // ✅ appendix halaman 2 (json string dari textarea)
            'certificate_appendix' => ['nullable', 'json'],
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

        $data['created_by'] = auth()->id();

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
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'location'    => ['nullable', 'string', 'max:255'],
            'start_date'  => ['required', 'date'],
            'end_date'    => ['nullable', 'date', 'after_or_equal:start_date'],
            'status'      => ['required', 'in:draft,active,closed'],
            'description' => ['nullable', 'string'],

            'certificate_template_id' => ['nullable', 'integer', 'exists:certificate_templates,id'],

            // ✅ appendix halaman 2
            'certificate_appendix' => ['nullable', 'json'],
        ]);

        if (!empty($data['certificate_template_id'])) {
            $isActive = CertificateTemplate::where('id', $data['certificate_template_id'])
                ->where('is_active', 1)
                ->exists();

            if (!$isActive) {
                return back()->withInput()->with('error', 'Template yang dipilih sedang nonaktif.');
            }
        }

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
}