<?php

namespace Database\Seeders;

use App\Models\Official;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class OfficialSeeder extends Seeder
{
    /**
     * Current officials as of November 2026, each name/position confirmed by 2 sources
     * (empatlawangkab.go.id news, detik, jpnn, tribunnews, klikempatlawang). Research notes:
     * workspace shared/riset-pejabat-empatlawang/README.md.
     * Order 1–3 = leaders row on /profil-pejabat. Photos: official portraits from Wikimedia Commons
     * (public domain), cropped 3:4 into assets/officials/; no portrait found for the others.
     */
    public function run(): void
    {
        $officials = [
            [
                'name' => 'H. Joncik Muhammad',
                'position' => 'Bupati Empat Lawang',
                'bio' => 'Bupati Empat Lawang periode 2025–2030, dilantik Gubernur Sumatera Selatan Herman Deru di Griya Agung Palembang pada 16 Juni 2025 berdasarkan SK Mendagri Nomor 100.1.3-2379 Tahun 2025. Sebelumnya menjabat Bupati Empat Lawang periode 2018–2023. Melanjutkan visi "Empat Lawang Madani Jilid II".',
                'order' => 1,
                'photo' => 'officials/bupati.jpg',
            ],
            [
                'name' => 'A. Rifai',
                'position' => 'Wakil Bupati Empat Lawang',
                'bio' => 'Wakil Bupati Empat Lawang periode 2025–2030, dilantik bersama Bupati H. Joncik Muhammad pada 16 Juni 2025 di Griya Agung Palembang.',
                'order' => 2,
                'photo' => 'officials/wakil-bupati.jpg',
            ],
            [
                'name' => 'Fauzan Khoiri Denin',
                'position' => 'Sekretaris Daerah Kabupaten Empat Lawang',
                'bio' => 'Sekretaris Daerah (Sekda) Kabupaten Empat Lawang, unsur pendamping Bupati dan Wakil Bupati dalam koordinasi perangkat daerah.',
                'order' => 3,
                'photo' => 'officials/sekda.jpg',
            ],
            [
                'name' => 'M. Yunidi Nuzli',
                'position' => 'Asisten Pemerintahan dan Kesejahteraan Rakyat Setda Kabupaten Empat Lawang',
                'bio' => 'Dilantik sebagai Asisten Pemerintahan dan Kesejahteraan Rakyat Sekretariat Daerah oleh Bupati Empat Lawang pada 12 Mei 2026.',
                'order' => 4,
            ],
            [
                'name' => 'Sumitro Sukma Bahagia',
                'position' => 'Kepala Dinas Komunikasi, Informatika, Statistik dan Persandian Kabupaten Empat Lawang',
                'bio' => 'Menjabat Plt Kepala Dinas Kominfo SP sejak serah terima jabatan Desember 2025, lalu dilantik sebagai Kepala Dinas definitif pada 12 Mei 2026.',
                'order' => 5,
            ],
        ];

        // Keyed by position: re-seeding replaces the previous holder instead of adding a duplicate.
        foreach ($officials as $data) {
            $photo = $data['photo'] ?? null;
            if ($photo && ! Storage::disk('public')->exists($photo)) {
                Storage::disk('public')->put($photo, file_get_contents(__DIR__ . '/assets/' . $photo));
            }

            Official::updateOrCreate(
                ['position' => $data['position']],
                $data + ['is_published' => true]
            );
        }

        $this->seedMore();
    }
    /**
     * Heads of agencies, Forkopimda, DPRD and camat (assets/officials-more.php). firstOrCreate, so a re-seed never
     * overwrites what an officer edited in admin. Names with only one source (or in conflict) are created hidden.
     */
    private function seedMore(): void
    {
        $order = [];
        foreach (require __DIR__ . '/assets/officials-more.php' as [$position, $name, $group, $since, $status, $published]) {
            $order[$group] = ($order[$group] ?? ($group === 'pemkab' ? 9 : 0)) + 1;
            $bio = $since ? 'Menjabat sejak ' . \Illuminate\Support\Carbon::parse($since)->locale('id')->translatedFormat('j F Y') . ($status === 'plt' ? ' (Plt).' : '.') : null;
            Official::firstOrCreate(['position' => $position], ['name' => $name, 'group' => $group, 'bio' => $bio, 'order' => $order[$group], 'is_published' => $published]);
        }
    }
}
