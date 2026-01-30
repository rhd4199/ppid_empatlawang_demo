<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PpidSetting;

class PpidSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PpidSetting::create(['key' => 'site_name', 'value' => 'PPID Kabupaten Empat Lawang']);
        PpidSetting::create(['key' => 'address', 'value' => 'Jl. Poros No. 1, Tebing Tinggi, Empat Lawang']);
        PpidSetting::create(['key' => 'email', 'value' => 'ppid@empatlawangkab.go.id']);
        PpidSetting::create(['key' => 'phone', 'value' => '(0702) 123456']);
    }
}
