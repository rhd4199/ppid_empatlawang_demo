<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PpidSetting;

class PpidSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PpidSetting::firstOrCreate(['key' => 'site_name'], ['value' => 'PPID Kabupaten Empat Lawang']);
        PpidSetting::firstOrCreate(['key' => 'address'], ['value' => 'Jl. Poros No. 1, Tebing Tinggi, Empat Lawang']);
        PpidSetting::firstOrCreate(['key' => 'email'], ['value' => 'ppid@empatlawangkab.go.id']);
        PpidSetting::firstOrCreate(['key' => 'phone'], ['value' => '(0702) 123456']);
    }
}
