<?php

namespace App\Support;

use App\Models\CitizenReport;
use App\Models\WaConversation;

/**
 * Tool catat_laporan: Sapa Madani records a citizen report after asking for the details (what, where, since when, who),
 * so officers follow it up in /admin/laporan-warga. Recording is the main follow-up; outside channels (SP4N-LAPOR!) come after.
 */
class ReportDesk
{
    public const TOOL = ['type' => 'function', 'function' => [
        'name' => 'catat_laporan',
        'description' => 'Catat laporan/aduan warga ke sistem PPID (mendapat kode laporan) SETELAH Anda menanyakan rinciannya: apa masalahnya, lokasi tepat (desa/kelurahan, kecamatan, nama jalan atau patokan), sejak kapan, dampaknya, dan nama pelapor. Panggil lagi dengan kode yang sama bila warga menambah keterangan.',
        'parameters' => ['type' => 'object', 'properties' => [
            'kategori' => ['type' => 'string', 'enum' => ['infrastruktur', 'pelayanan', 'pungli', 'lingkungan', 'bencana', 'sosial', 'lainnya']],
            'uraian' => ['type' => 'string', 'description' => 'Ringkasan masalah dengan kata-kata warga, termasuk dampaknya'],
            'lokasi' => ['type' => 'string', 'description' => 'Nama jalan / patokan / dusun'],
            'kecamatan' => ['type' => 'string'],
            'desa' => ['type' => 'string'],
            'waktu_kejadian' => ['type' => 'string', 'description' => 'mis. "sejak 2 minggu lalu"'],
            'nama_pelapor' => ['type' => 'string'],
            'kontak' => ['type' => 'string', 'description' => 'Nomor HP pelapor (hanya chat situs; di WhatsApp nomor otomatis)'],
            'kode' => ['type' => 'string', 'description' => 'Isi bila memperbarui laporan yang sudah dicatat'],
        ], 'required' => ['kategori', 'uraian', 'lokasi']],
    ]];

    public static function record(array $args, ?WaConversation $person, string $channel): array
    {
        $text = fn (string $key, int $max) => trim(mb_substr(preg_replace('/\s+/u', ' ', (string) ($args[$key] ?? '')), 0, $max));
        [$description, $location] = [$text('uraian', 2000), $text('lokasi', 500)];
        if (mb_strlen($description) < 10 || mb_strlen($location) < 3) {
            return ['error' => 'Rincian belum cukup. Tanyakan dulu apa masalahnya dan lokasi tepatnya (desa/kecamatan, jalan/patokan).'];
        }
        $data = array_filter([
            'category' => isset(CitizenReport::CATEGORIES[$args['kategori'] ?? '']) ? $args['kategori'] : 'lainnya',
            'description' => $description,
            'location' => $location,
            'kecamatan' => $text('kecamatan', 100),
            'village' => $text('desa', 100),
            'occurred' => $text('waktu_kejadian', 150),
            'reporter_name' => $text('nama_pelapor', 100) ?: $person?->displayName(),
            'contact' => $person ? '+' . $person->phone : (WhatsAppGateway::normalize($text('kontak', 30)) ?: null),
        ], fn ($v) => $v !== null && $v !== '');

        // Same chat adds details to its open report instead of creating a second one.
        $report = ($code = $text('kode', 20)) ? CitizenReport::where('code', $code)->where('status', 'baru')
            ->when($person, fn ($q) => $q->where('wa_conversation_id', $person->id))->first() : null;
        if ($report) {
            $report->update($data);
        } else {
            $report = CitizenReport::create($data + ['code' => CitizenReport::nextCode(), 'channel' => $channel, 'wa_conversation_id' => $person?->id]);
        }
        $person?->update(['label' => 'aduan']);

        return ['kode' => $report->code, 'status' => CitizenReport::STATUSES[$report->status ?? 'baru'],
            'langkah_berikut' => 'Sampaikan kode ini ke warga. Petugas PPID akan meneruskan laporan ke instansi terkait dan mengabari lewat chat ini. Setelah itu sebutkan opsi tambahan: kirim foto ke chat ini, atau lapor juga lewat SP4N-LAPOR! (lapor.go.id).'];
    }
}
