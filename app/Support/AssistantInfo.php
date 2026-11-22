<?php

namespace App\Support;

use App\Models\ContactSetting;
use App\Models\Official;

/**
 * Sapa Madani fallback answers for general office info: contact, hours/holidays, location, fees, processing time,
 * officials/PPID and visiting in person. Data comes from ContactSetting and Official; fixed texts are worded as on
 * the Standar Layanan page (AssistantTest checks the page still says them). Never invent numbers or names.
 */
class AssistantInfo
{
    public const FEE = 'Layanan informasi publik di Kabupaten Empat Lawang tidak dipungut biaya (GRATIS), kecuali untuk biaya penggandaan atau perekaman dokumen yang timbul sesuai dengan peraturan perundang-undangan yang berlaku.';

    public const DURATION = 'Permohonan diproses dan dijawab paling lambat 10 + 7 hari kerja (apabila informasi belum dikuasai/didokumentasikan).';

    public const HOLIDAY = 'Layanan tutup pada hari libur nasional dan cuti bersama sesuai keputusan pemerintah pusat.';

    /** Answer for a general-info question, or null to let other topics try. */
    public static function reply(string $text): ?string
    {
        $standard = route('standard-service.index');

        // "minta data biaya perjalanan dinas" is a document request, not a fee question
        if (preg_match('/\b(biaya|biayanya|bayar|bayarnya|berbayar|gratis|tarif|ongkos|dipungut|harga)\b/u', $text) && ! preg_match('/\b(data|dokumen|laporan|anggaran|apbd|perjalanan)\b/u', $text)) {
            return self::FEE . " Rinciannya ada di halaman Standar Layanan ({$standard}#biaya).";
        }
        if (preg_match('/\b(berapa lama|berapa hari|lamanya|lama (proses|prosesnya|diproses|dijawab|jawab)|waktu proses|jangka waktu|kapan (selesai|dijawab|diproses|keluar))\b/u', $text)) {
            return self::DURATION . ' Bila jawabannya belum sesuai, keberatan dapat diajukan dan Atasan PPID menanggapi paling lambat 30 hari kerja.'
                . ' Status permohonan bisa dicek di menu Cek Status Permohonan (' . route('request.status') . "). Alur lengkapnya ada di halaman Standar Layanan ({$standard}#alur).";
        }
        if ($official = self::official($text)) {
            return $official;
        }
        if (preg_match('/\b(datang langsung|datang ke kantor|ke kantor|langsung ke kantor|offline|tatap muka|meja layanan|loket)\b/u', $text)) {
            return self::contact('alamat jam') . ' Saat datang, bawa KTP dan sampaikan informasi yang dibutuhkan beserta tujuannya kepada petugas.'
                . " Formulir permohonan offline dapat diunduh di halaman Standar Layanan ({$standard}#permohonan).";
        }
        if (preg_match('/\b(sabtu|minggu|libur|tanggal merah|cuti bersama|lebaran|hari raya)\b/u', $text)) {
            return self::contact('jam') . ' ' . self::HOLIDAY;
        }
        if (preg_match('/\b(kontak|telepon|telpon|alamat|alamatnya|email|e-mail|surel|jam|buka|tutup|kantor|kantornya|lokasi|lokasinya|dimana|di mana|peta|maps|whatsapp|wa|hubungi)\b/u', $text)) {
            return self::contact($text);
        }

        return null;
    }

    /** Officials by name from the Official table; "siapa PPID" → PPID structure. Null when not asked. */
    private static function official(string $text): ?string
    {
        // Officials outside our table (police, prosecutor, DPRD, camat, ...): say so instead of guessing a name.
        if (preg_match('/\bsiapa\b/u', $text) && preg_match('/\b(kapolres|kapolsek|kapolda|polres|polsek|kajari|kejari|kejaksaan|dandim|kodim|danramil|ketua (dprd|pengadilan)|dprd|camat|kades|kepala desa|lurah|direktur rsud|gubernur|kepala kua)\b/u', $text, $m)) {
            $office = ['kapolres' => 'Kapolres', 'polres' => 'Kapolres', 'kapolsek' => 'Kapolsek', 'polsek' => 'Kapolsek', 'kajari' => 'Kajari', 'kejari' => 'Kajari', 'dandim' => 'Dandim', 'kodim' => 'Dandim', 'ketua dprd' => 'Ketua DPRD', 'dprd' => 'Ketua DPRD', 'direktur rsud' => 'Direktur RSUD', 'kepala kua' => 'Kepala KUA'][$m[1]] ?? ucfirst($m[1]);

            return "Mohon maaf, nama {$office} belum ada di data pejabat kami, jadi saya tidak bisa memastikannya agar tidak keliru. "
                . 'Informasi resminya bisa dilihat di situs atau media sosial resmi instansi tersebut. Data pejabat Pemerintah Kabupaten Empat Lawang yang tersedia ada di halaman Profil Pejabat (' . route('officials.index') . ').';
        }
        $ppid = preg_match('/\b(ppid utama|atasan ppid|siapa ppid|siapa (kepala|pejabat) ppid|ppid nya|ppidnya)\b/u', $text);
        $role = preg_match('/\b(bupati|wakil bupati|wabup|sekda|sekretaris daerah|kadis|kepala dinas|pejabat)\b/u', $text);
        if (! $ppid && ! ($role && preg_match('/\b(siapa|nama|pejabat)\b/u', $text))) {
            return null;
        }
        $officials = Official::where('is_published', true)->orderBy('order')->get(['name', 'position']);
        $holder = fn (string $like) => $officials->first(fn ($o) => preg_match($like, mb_strtolower($o->position)));
        $named = fn ($o) => $o ? " (saat ini {$o->name})" : '';
        $more = ' Profil pejabat ada di halaman Profil Pejabat (' . route('officials.index') . ').';

        if ($ppid) {
            return 'PPID Utama Kabupaten Empat Lawang dijabat Kepala Dinas Komunikasi dan Informatika' . $named($holder('/^kepala dinas komunikasi/u'))
                . ', dengan Atasan PPID Sekretaris Daerah' . $named($holder('/^sekretaris daerah/u')) . '.' . $more;
        }
        $asked = [
            '/\b(wakil bupati|wabup)\b/u' => '/^wakil bupati/u',
            '/\bbupati\b/u' => '/^bupati/u',
            '/\b(sekda|sekretaris daerah)\b/u' => '/^sekretaris daerah/u',
            '/\b(kadis|kepala dinas)\b/u' => '/^kepala dinas/u',
        ];
        foreach ($asked as $question => $position) {
            if (preg_match($question, $text) && $o = $holder($position)) {
                return "{$o->position}: {$o->name}." . $more;
            }
        }
        if ($officials->isEmpty()) {
            return 'Data pejabat ada di halaman Profil Pejabat (' . route('officials.index') . ').';
        }

        // First 6 (leaders); the rest on the page, so a WhatsApp reply does not list dozens of names.
        return "Pejabat Kabupaten Empat Lawang:\n" . $officials->take(6)->map(fn ($o) => "- {$o->position}: {$o->name}")->implode("\n")
            . ($officials->count() > 6 ? "\nDaftar lengkap pejabat Pemkab, Forkopimda, DPRD dan camat ada di halaman Profil Pejabat." : '') . "\n" . trim($more);
    }

    /** Answers with the ContactSetting data that was asked for (all of it for a plain "kontak"); links to /kontak only when it is missing. */
    private static function contact(string $text): string
    {
        $c = ContactSetting::first();
        $fields = [
            'hours' => ['/\b(jam|buka|tutup|hari)\b/u', implode('; ', (array) $c?->working_hours), 'Jam layanan PPID: %s.'],
            'address' => ['/\b(alamat|alamatnya|lokasi|lokasinya|kantor|kantornya|datang|dimana|di mana|peta|maps)\b/u', trim((string) $c?->address), 'Kantor PPID beralamat di %s.'],
            'phones' => ['/\b(telepon|telpon|nomor|hp|whatsapp|wa|hubungi)\b/u', implode(' atau ', (array) $c?->phones), 'PPID dapat dihubungi di nomor %s.'],
            'emails' => ['/\b(email|e-mail|surel)\b/u', implode(' atau ', (array) $c?->emails), 'Email PPID: %s.'],
        ];
        $asked = array_filter($fields, fn ($f) => preg_match($f[0], $text)) ?: $fields;

        $sentences = [];
        foreach ($asked as [, $value, $format]) {
            if ($value !== '') {
                $sentences[] = sprintf($format, $value);
            }
        }
        if (! $sentences) {
            return 'Data kontak lengkap (alamat, telepon, email, dan jam layanan) ada di halaman Kontak (' . route('contact.index') . ').';
        }
        if (array_keys($asked) === ['hours'] && $fields['address'][1] !== '') {
            $sentences[] = 'Kalau ingin datang langsung, alamatnya di ' . $fields['address'][1] . '.';
        }
        if (isset($asked['address']) && $fields['address'][1] !== '') {
            $sentences[] = 'Peta lokasinya ada di halaman Kontak (' . route('contact.index') . '#map-section).';
        } elseif (count($sentences) < count($asked)) {
            $sentences[] = 'Info kontak lainnya ada di halaman Kontak (' . route('contact.index') . ').';
        }

        return implode(' ', $sentences);
    }
}
