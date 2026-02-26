<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CertificateTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CertificateTemplateController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $status = $request->query('status', ''); // active/inactive/all

        $templates = CertificateTemplate::query()
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('code', 'like', "%{$q}%");
                });
            })
            ->when(in_array($status, ['active','inactive'], true), function ($qq) use ($status) {
                $qq->where('is_active', $status === 'active');
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.templates.index', compact('templates', 'q', 'status'));
    }

    public function create()
    {
        return view('admin.templates.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required','string','max:255'],
            'code'        => ['nullable','string','max:50','unique:certificate_templates,code'],
            'description' => ['nullable','string'],

            // background optional
            'background'  => ['nullable','file','mimes:png,jpg,jpeg,pdf','max:5120'],

            // setting basic
            'paper_size'  => ['nullable','in:A4,A5,LETTER'],
            'orientation' => ['nullable','in:landscape,portrait'],
            'pos_name_x'  => ['nullable','integer','min:0','max:5000'],
            'pos_name_y'  => ['nullable','integer','min:0','max:5000'],
            'pos_qr_x'    => ['nullable','integer','min:0','max:5000'],
            'pos_qr_y'    => ['nullable','integer','min:0','max:5000'],
        ]);

        $code = $data['code'] ?? null;
        if (!$code) {
            $code = 'TPL-' . strtoupper(Str::random(6));
        }

        $backgroundPath = null;
        if ($request->hasFile('background')) {
            $backgroundPath = $request->file('background')->store('templates', 'public');
        }

        $settings = [
            'paper_size'  => $data['paper_size'] ?? 'A4',
            'orientation' => $data['orientation'] ?? 'landscape',
            'pos' => [
                'name' => [
                    'x' => (int)($data['pos_name_x'] ?? 0),
                    'y' => (int)($data['pos_name_y'] ?? 0),
                ],
                'qr' => [
                    'x' => (int)($data['pos_qr_x'] ?? 0),
                    'y' => (int)($data['pos_qr_y'] ?? 0),
                ],
            ],
        ];

        CertificateTemplate::create([
            'name'            => $data['name'],
            'code'            => $code,
            'description'     => $data['description'] ?? null,
            'background_path' => $backgroundPath,
            'settings'        => $settings,
            'is_active'       => true,
            'created_by'      => auth()->id(),
        ]);

        return redirect()->route('admin.templates.index')->with('success', 'Template berhasil dibuat.');
    }

    public function edit(CertificateTemplate $template)
    {
        return view('admin.templates.edit', compact('template'));
    }

    public function update(Request $request, CertificateTemplate $template)
    {
        $data = $request->validate([
            'name'        => ['required','string','max:255'],
            'code'        => ['required','string','max:50','unique:certificate_templates,code,' . $template->id],
            'description' => ['nullable','string'],

            'background'  => ['nullable','file','mimes:png,jpg,jpeg,pdf','max:5120'],

            'paper_size'  => ['nullable','in:A4,A5,LETTER'],
            'orientation' => ['nullable','in:landscape,portrait'],
            'pos_name_x'  => ['nullable','integer','min:0','max:5000'],
            'pos_name_y'  => ['nullable','integer','min:0','max:5000'],
            'pos_qr_x'    => ['nullable','integer','min:0','max:5000'],
            'pos_qr_y'    => ['nullable','integer','min:0','max:5000'],
        ]);

        $backgroundPath = $template->background_path;
        if ($request->hasFile('background')) {
            // hapus lama jika ada
            if ($backgroundPath && Storage::disk('public')->exists($backgroundPath)) {
                Storage::disk('public')->delete($backgroundPath);
            }
            $backgroundPath = $request->file('background')->store('templates', 'public');
        }

        $old = $template->settings ?? [];
        $settings = [
            'paper_size'  => $data['paper_size'] ?? ($old['paper_size'] ?? 'A4'),
            'orientation' => $data['orientation'] ?? ($old['orientation'] ?? 'landscape'),
            'pos' => [
                'name' => [
                    'x' => (int)($data['pos_name_x'] ?? data_get($old, 'pos.name.x', 0)),
                    'y' => (int)($data['pos_name_y'] ?? data_get($old, 'pos.name.y', 0)),
                ],
                'qr' => [
                    'x' => (int)($data['pos_qr_x'] ?? data_get($old, 'pos.qr.x', 0)),
                    'y' => (int)($data['pos_qr_y'] ?? data_get($old, 'pos.qr.y', 0)),
                ],
            ],
        ];

        $template->update([
            'name'            => $data['name'],
            'code'            => $data['code'],
            'description'     => $data['description'] ?? null,
            'background_path' => $backgroundPath,
            'settings'        => $settings,
        ]);

        return redirect()->route('admin.templates.index')->with('success', 'Template berhasil diperbarui.');
    }

    public function toggleActive(CertificateTemplate $template)
    {
        $template->update(['is_active' => !$template->is_active]);
        return back()->with('success', 'Status template diperbarui.');
    }

    public function destroy(CertificateTemplate $template)
    {
        // aman: kalau dipakai event, sebaiknya jangan hapus
        $isUsed = $template->events()->exists();
        if ($isUsed) {
            return back()->with('error', 'Template sedang dipakai oleh event. Nonaktifkan saja atau lepas dari event dulu.');
        }

        if ($template->background_path && Storage::disk('public')->exists($template->background_path)) {
            Storage::disk('public')->delete($template->background_path);
        }

        $template->delete();
        return back()->with('success', 'Template berhasil dihapus.');
    }
}
