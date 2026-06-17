<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Check if user is super admin (role id = 1 or matching specific permission)
        if (auth()->user()->role_id !== 1) {
            abort(403, 'Unauthorized action. Only Super Admin can access settings.');
        }

        $strictTteDate = Setting::getValue('strict_tte_date', false);
        $mediumTteDate = Setting::getValue('medium_tte_date', false);
        $reuseDeletedNumbers = Setting::getValue('reuse_deleted_numbers', false);
        $allowDuplicateParticipants = Setting::getValue('allow_duplicate_participants', false);
        
        $fontNameParticipant = Setting::getValue('font_name_participant', "'Great Vibes', cursive");
        $fontRoleParticipant = Setting::getValue('font_role_participant', "'Alex Brush', cursive");

        // Cari nomor sertifikat yang belum terpakai (bolong/missing)
        $certs = \App\Models\Certificate::whereNotNull('sequence')
            ->pluck('sequence')
            ->toArray();
        $maxSequence = max($certs ?: [0]);
        $missingSequences = [];
        for ($i = 1; $i <= $maxSequence; $i++) {
            if (!in_array($i, $certs)) {
                $missingSequences[] = $i;
            }
        }

        // Cari sertifikat yang memiliki status anomali (selisih di dashboard)
        $anomalyCerts = \App\Models\Certificate::with(['participant', 'event'])
            ->whereNotIn('status', ['draft', 'submitted', 'approved', 'signed', 'terbit', 'final_generated', 'sent'])
            ->get();

        return view('admin.system.settings.index', compact(
            'strictTteDate', 'mediumTteDate', 'reuseDeletedNumbers', 'allowDuplicateParticipants', 
            'missingSequences', 'maxSequence', 'anomalyCerts', 'fontNameParticipant', 'fontRoleParticipant'
        ));
    }

    public function update(Request $request)
    {
        if (auth()->user()->role_id !== 1) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'strict_tte_date' => 'nullable|boolean',
            'medium_tte_date' => 'nullable|boolean',
            'reuse_deleted_numbers' => 'nullable|boolean',
            'allow_duplicate_participants' => 'nullable|boolean',
            'font_name_participant' => 'nullable|string|max:255',
            'font_role_participant' => 'nullable|string|max:255',
        ]);

        $strictVal = $request->has('strict_tte_date') ? '1' : '0';
        $mediumVal = $request->has('medium_tte_date') ? '1' : '0';

        // Auto-disable medium validation if strict validation is enabled
        if ($strictVal === '1') {
            $mediumVal = '0';
        }

        Setting::updateOrCreate(
            ['key' => 'strict_tte_date'],
            [
                'value' => $strictVal,
                'type' => 'boolean'
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'medium_tte_date'],
            [
                'value' => $mediumVal,
                'type' => 'boolean'
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'reuse_deleted_numbers'],
            [
                'value' => $request->has('reuse_deleted_numbers') ? '1' : '0',
                'type' => 'boolean'
            ]
        );

        Setting::updateOrCreate(
            ['key' => 'allow_duplicate_participants'],
            [
                'value' => $request->has('allow_duplicate_participants') ? '1' : '0',
                'type' => 'boolean'
            ]
        );

        if ($request->has('font_name_participant')) {
            Setting::updateOrCreate(
                ['key' => 'font_name_participant'],
                [
                    'value' => $request->input('font_name_participant'),
                    'type' => 'string'
                ]
            );
        }

        if ($request->has('font_role_participant')) {
            Setting::updateOrCreate(
                ['key' => 'font_role_participant'],
                [
                    'value' => $request->input('font_role_participant'),
                    'type' => 'string'
                ]
            );
        }

        return redirect()->back()->with('success', 'Pengaturan sistem berhasil diperbarui.');
    }
}
