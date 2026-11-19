<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactSetting;
use App\Models\PpidSetting;
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

        $satisfactionIndex = PpidSetting::where('key', 'stat_satisfaction_index')->value('value') ?? '98%';

        $assistant = assistant_settings();

        return view('admin.contact-settings.index', compact('settings', 'satisfactionIndex', 'assistant'));
    }

    public function updateStats(Request $request)
    {
        $data = $request->validate([
            'stat_satisfaction_index' => 'nullable|string|max:20',
        ]);

        PpidSetting::updateOrCreate(
            ['key' => 'stat_satisfaction_index'],
            ['value' => $data['stat_satisfaction_index'] ?? '98%', 'description' => 'Indeks Kepuasan Masyarakat ditampilkan di beranda']
        );

        return redirect()->route('admin.contact-settings.index')->with('success', 'Statistik beranda berhasil diperbarui.');
    }

    public function updateAssistant(Request $request)
    {
        $data = $request->validate([
            'assistant_enabled' => 'nullable|boolean',
            'assistant_whatsapp' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s()]*$/'],
            'assistant_api_url' => 'nullable|url:http,https|max:500',
        ]);

        $values = [
            'assistant_enabled' => $request->boolean('assistant_enabled') ? '1' : '0',
            'assistant_whatsapp' => trim($data['assistant_whatsapp'] ?? ''),
            'assistant_api_url' => trim($data['assistant_api_url'] ?? ''),
        ];
        foreach ($values as $key => $value) {
            PpidSetting::updateOrCreate(['key' => $key], ['value' => $value, 'description' => 'Sapa Madani (asisten virtual)']);
        }

        return redirect()->route('admin.contact-settings.index')->with('success', 'Pengaturan Sapa Madani berhasil diperbarui.');
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
            // URL domain wins over the dropdown, see social_platform()
            foreach ($data['social_media'] as &$item) {
                $item['platform'] = social_platform($item);
            }
            unset($item);
        }

        $settings->update($data);

        return redirect()->back()->with('success', 'Pengaturan kontak berhasil diperbarui.');
    }
}
