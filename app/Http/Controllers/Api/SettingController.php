<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactSetting;
use App\Models\PpidSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function showContact()
    {
        return ContactSetting::firstOrCreate([], [
            'phones' => [], 'emails' => [], 'working_hours' => [], 'social_media' => [],
        ]);
    }

    public function updateContact(Request $request)
    {
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

        foreach (['phones', 'emails', 'working_hours', 'social_media'] as $k) {
            if (array_key_exists($k, $data)) {
                $data[$k] = array_values(array_filter($data[$k] ?? []));
            }
        }

        if (isset($data['social_media'])) {
            $data['social_media'] = array_map(fn ($s) => ['platform' => social_platform($s)] + $s, $data['social_media']);
        }

        $settings = $this->showContact();
        $settings->update($data);

        return $settings;
    }

    public function showStats()
    {
        return ['stat_satisfaction_index' => PpidSetting::where('key', 'stat_satisfaction_index')->value('value') ?? '98%'];
    }

    public function updateStats(Request $request)
    {
        $data = $request->validate(['stat_satisfaction_index' => 'required|string|max:20']);

        PpidSetting::updateOrCreate(
            ['key' => 'stat_satisfaction_index'],
            ['value' => $data['stat_satisfaction_index'], 'description' => 'Indeks Kepuasan Masyarakat ditampilkan di beranda']
        );

        return $this->showStats();
    }
}
