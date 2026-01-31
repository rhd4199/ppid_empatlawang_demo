<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactSetting;
use Illuminate\Http\Request;

class ContactSettingController extends Controller
{
    public function index()
    {
        $settings = ContactSetting::first();
        if (!$settings) {
            // Should not happen if seeded, but create default if missing
            $settings = ContactSetting::create([
                'phones' => [],
                'emails' => [],
                'social_media' => [],
                'working_hours' => []
            ]);
        }
        return view('admin.contact-settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $settings = ContactSetting::firstOrFail();

        $data = $request->validate([
            'address' => 'nullable|string',
            'maps_embed' => 'nullable|string',
            'phones' => 'nullable|array',
            'phones.*' => 'nullable|string',
            'emails' => 'nullable|array',
            'emails.*' => 'nullable|email',
            'working_hours' => 'nullable|array',
            'working_hours.*' => 'nullable|string',
            'social_media' => 'nullable|array',
            'social_media.*.platform' => 'required|string',
            'social_media.*.name' => 'required|string',
            'social_media.*.username' => 'nullable|string',
            'social_media.*.url' => 'nullable|string',
            'social_media.*.icon' => 'nullable|string',
            'social_media.*.color' => 'nullable|string',
        ]);

        // Filter out empty entries
        $data['phones'] = array_values(array_filter($data['phones'] ?? [], fn($v) => !empty($v)));
        $data['emails'] = array_values(array_filter($data['emails'] ?? [], fn($v) => !empty($v)));
        $data['working_hours'] = array_values(array_filter($data['working_hours'] ?? [], fn($v) => !empty($v)));
        
        // Filter social media (must have platform and name)
        if (isset($data['social_media'])) {
            $data['social_media'] = array_values(array_filter($data['social_media'], function($item) {
                return !empty($item['platform']) && !empty($item['name']);
            }));
        }

        $settings->update($data);

        return redirect()->back()->with('success', 'Pengaturan kontak berhasil diperbarui.');
    }
}
