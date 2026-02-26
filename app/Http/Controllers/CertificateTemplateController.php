<?php

namespace App\Http\Controllers;

use App\Models\CertificateTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CertificateTemplateController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $templates = CertificateTemplate::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('code', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('templates.index', compact('templates', 'q'));
    }

    public function create()
    {
        return view('templates.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'code'        => ['required', 'string', 'max:50', Rule::unique('certificate_templates', 'code')],
            'description' => ['nullable', 'string'],
            'is_active'   => ['nullable'], // akan diproses boolean()
            'settings'    => ['nullable', 'json'], // wajib JSON valid kalau diisi

            // dukung 2 nama input file: file / background
            'file'        => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp,pdf', 'max:5120'],
            'background'  => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp,pdf', 'max:5120'],
        ]);

        $data['code'] = strtoupper(trim($data['code']));
        $data['is_active'] = $request->boolean('is_active', true);

        // settings disimpan sebagai array (lebih enak), DB json/text sama-sama aman
        $settings = null;
        if (!empty($data['settings'])) {
            $settings = json_decode($data['settings'], true);
        }

        // upload background
        $filePath = null;
        $upload = $request->file('file') ?: $request->file('background');
        if ($upload) {
            $filePath = $upload->store('certificate-templates', 'public');
        }

        CertificateTemplate::create([
            'name'        => $data['name'],
            'code'        => $data['code'],
            'description' => $data['description'] ?? null,
            'file_path'   => $filePath,
            'is_active'   => $data['is_active'],
            'settings'    => $settings,
            'created_by'  => auth()->id(),
        ]);

        return redirect()
            ->route('admin.system.templates.index')
            ->with('success', 'Template berhasil ditambahkan.');
    }

    public function show(CertificateTemplate $template)
    {
        return view('templates.show', compact('template'));
    }

    public function edit(CertificateTemplate $template)
    {
        return view('templates.edit', compact('template'));
    }

    public function update(Request $request, CertificateTemplate $template)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'code'        => ['required', 'string', 'max:50', Rule::unique('certificate_templates', 'code')->ignore($template->id)],
            'description' => ['nullable', 'string'],
            'is_active'   => ['nullable'],
            'settings'    => ['nullable', 'json'],

            'file'        => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp,pdf', 'max:5120'],
            'background'  => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp,pdf', 'max:5120'],
        ]);

        $data['code'] = strtoupper(trim($data['code']));
        $isActive = $request->has('is_active')
            ? $request->boolean('is_active')
            : (bool) $template->is_active;

        $settings = $template->settings;
        if (array_key_exists('settings', $data)) {
            $settings = !empty($data['settings']) ? json_decode($data['settings'], true) : null;
        }

        // file path lama
        $filePath = $template->file_path;

        $upload = $request->file('file') ?: $request->file('background');
        if ($upload) {
            // hapus file lama kalau ada
            if ($filePath && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
            $filePath = $upload->store('certificate-templates', 'public');
        }

        $template->update([
            'name'        => $data['name'],
            'code'        => $data['code'],
            'description' => $data['description'] ?? null,
            'file_path'   => $filePath,
            'is_active'   => $isActive,
            'settings'    => $settings,
        ]);

        return redirect()
            ->route('admin.system.templates.index')
            ->with('success', 'Template berhasil diperbarui.');
    }

    public function destroy(CertificateTemplate $template)
    {
        if ($template->file_path && Storage::disk('public')->exists($template->file_path)) {
            Storage::disk('public')->delete($template->file_path);
        }

        $template->delete();

        return redirect()
            ->route('admin.system.templates.index')
            ->with('success', 'Template berhasil dihapus.');
    }

    public function toggle(CertificateTemplate $template)
    {
        $template->update(['is_active' => !$template->is_active]);

        return back()->with('success', 'Status template berhasil diubah.');
    }
}