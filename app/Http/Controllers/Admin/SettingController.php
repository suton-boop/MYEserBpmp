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
        $reuseDeletedNumbers = Setting::getValue('reuse_deleted_numbers', false);

        return view('admin.system.settings.index', compact('strictTteDate', 'reuseDeletedNumbers'));
    }

    public function update(Request $request)
    {
        if (auth()->user()->role_id !== 1) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'strict_tte_date' => 'nullable|boolean',
            'reuse_deleted_numbers' => 'nullable|boolean'
        ]);

        Setting::updateOrCreate(
            ['key' => 'strict_tte_date'],
            [
                'value' => $request->has('strict_tte_date') ? '1' : '0',
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

        return redirect()->back()->with('success', 'Pengaturan sistem berhasil diperbarui.');
    }
}
