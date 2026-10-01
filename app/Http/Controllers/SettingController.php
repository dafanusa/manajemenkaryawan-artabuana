<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::pluck('value', 'key')->all();
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'app_title' => ['required', 'string', 'max:255'],
            'company_tagline' => ['nullable', 'string', 'max:255'],
            'company_email' => ['nullable', 'email'],
            'company_phone' => ['nullable', 'string'],
            'company_address' => ['nullable', 'string'],
        ]);

        foreach ($validated as $key => $val) {
            SystemSetting::set($key, $val);
        }

        ActivityLog::record('update_settings', 'Memperbarui pengaturan sistem dan profil perusahaan');

        return back()->with('success', 'Pengaturan sistem berhasil disimpan!');
    }
}
