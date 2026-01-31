<?php

namespace Database\Seeders;

use App\Models\ContactSetting;
use Illuminate\Database\Seeder;

class ContactSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (ContactSetting::count() > 0) {
            return;
        }

        ContactSetting::create([
            'address' => 'Jl. Lintas Sumatera No. 1, Tebing Tinggi, Empat Lawang',
            'phones' => ['(0702) 123456', '+62 812-3456-7890'],
            'emails' => ['diskominfo@empatlawangkab.go.id', 'ppid@empatlawangkab.go.id'],
            'working_hours' => ['Senin - Jumat: 08:00 - 16:00', 'Sabtu - Minggu: Tutup'],
            'maps_embed' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3983.5!2d103.0!3d-3.7!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zM8KwNDInMDAuMCJTIDEwM8KwMDAnMDAuMCJF!5e0!3m2!1sen!2sid!4v1620000000000!5m2!1sen!2sid',
            'social_media' => [
                [
                    'platform' => 'instagram',
                    'name' => 'Pemkab Empat Lawang',
                    'username' => '@empatlawang_kab',
                    'url' => '#',
                    'icon' => 'fab fa-instagram',
                    'color' => 'text-danger'
                ],
                [
                    'platform' => 'instagram',
                    'name' => 'Diskominfo',
                    'username' => '@kominfo_empatlawang',
                    'url' => '#',
                    'icon' => 'fab fa-instagram',
                    'color' => 'text-primary'
                ],
                [
                    'platform' => 'instagram',
                    'name' => 'PPID Utama',
                    'username' => '@ppid_empatlawang',
                    'url' => '#',
                    'icon' => 'fab fa-instagram',
                    'color' => 'text-success'
                ],
                [
                    'platform' => 'facebook',
                    'name' => 'Pemerintah Kab. Empat Lawang',
                    'username' => 'Official Page • 15K Followers',
                    'url' => '#',
                    'icon' => 'fab fa-facebook',
                    'color' => 'text-primary'
                ],
                [
                    'platform' => 'youtube',
                    'name' => 'Youtube',
                    'username' => 'Empat Lawang TV',
                    'url' => '#',
                    'icon' => 'fab fa-youtube',
                    'color' => 'text-danger'
                ],
                [
                    'platform' => 'twitter',
                    'name' => 'Twitter / X',
                    'username' => '@pemkab_4L',
                    'url' => '#',
                    'icon' => 'fab fa-twitter',
                    'color' => 'text-dark'
                ]
            ]
        ]);
    }
}
