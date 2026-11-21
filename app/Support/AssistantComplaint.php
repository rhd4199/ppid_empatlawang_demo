<?php

namespace App\Support;

/**
 * Sapa Madani fallback replies for citizen complaints ("mau melapor", "ada pungli").
 * "laporan keuangan" is a document, not a complaint, and is not matched here.
 * Channel texts are copied from the Kanal Pengaduan table in resources/views/standard_service/index.blade.php
 * (AssistantTest checks the page still shows them); never invent numbers.
 */
class AssistantComplaint
{
    public const ASK = 'Boleh saya tahu laporannya tentang apa?';

    private const VERBS = ['lapor', 'melapor', 'melaporkan', 'laporkan', 'mengadu', 'mengadukan', 'adukan', 'aduan', 'pengaduan', 'komplain'];

    // abuse words also count as a complaint on their own: "ada pungli di kantor desa"
    private const ABUSE = '/\b(wewenang|pungli|pungutan|korupsi|gratifikasi|suap|sogok|kkn|nepotisme|asn|pns|pejabat|pegawai|kode etik|disiplin)\b/u';

    private const FACILITY = '/\b(jalan|jembatan|lampu|pju|drainase|got|selokan|saluran|gorong|fasilitas|rusak|berlubang|banjir|longsor)\b/u';

    public const LAPOR = 'SP4N-LAPOR!: www.lapor.go.id, SMS ke 1708 (Telkomsel, Indosat, XL), atau Aplikasi SP4N-LAPOR! (Android/iOS)';

    public const INSPEKTORAT = 'Inspektorat Daerah Kabupaten Empat Lawang: Jl. Lintas Sumatera, Tebing Tinggi, Kabupaten Empat Lawang — datang langsung atau surat tertulis pada jam kerja';

    public const OMBUDSMAN = 'Ombudsman RI: www.ombudsman.go.id, Telepon 137, WhatsApp 0811-9553-737 (Perwakilan Sumatera Selatan)';

    /** Reply for a complaint, or null when the message is not one. */
    public static function reply(string $text): ?string
    {
        $abuse = (bool) preg_match(self::ABUSE, $text) && (self::hasVerb($text) || preg_match('/\b(wewenang|pungli|pungutan liar|gratifikasi|suap|sogok)\b/u', $text));
        if (! $abuse && ! self::hasVerb($text)) {
            return null;
        }
        $more = ' Alur dan kanal pengaduan lengkap ada di halaman Standar Layanan (' . route('standard-service.index') . '#pengaduan).';

        if ($abuse) {
            return 'Bisa, Bapak/Ibu. Aduan penyalahgunaan wewenang, pungutan liar, atau pelanggaran oleh ASN/pejabat dapat disampaikan ke:'
                . "\n1. " . self::INSPEKTORAT . "\n2. " . self::OMBUDSMAN . "\n3. " . self::LAPOR
                . "\nSiapkan: identitas pengadu (nama, NIK, alamat, nomor telepon/email aktif), nama dan/atau jabatan pejabat yang diadukan, uraian kejadian (apa, siapa, kapan, di mana, bagaimana), serta bukti pendukung (dokumen, foto, tangkapan layar, rekaman, saksi)."
                . ' Identitas pengadu dijaga kerahasiaannya dan hanya diketahui petugas yang menangani aduan.' . $more;
        }
        if (preg_match(self::FACILITY, $text)) {
            return 'Bisa, Bapak/Ibu. Laporan jalan, jembatan, lampu jalan, drainase, atau fasilitas umum yang rusak dapat disampaikan lewat ' . self::LAPOR . '.'
                . ' Sebutkan lokasi persisnya (nama jalan/desa/kecamatan), sejak kapan rusak, dan lampirkan foto. Simpan nomor tiket untuk memantau tindak lanjutnya.' . $more;
        }

        $prepare = 'Siapkan juga: apa yang terjadi, lokasi dan waktunya, pihak yang terlibat (bila ada), serta foto atau dokumen pendukung.';
        $subject = array_diff(DocumentFinder::keywords($text), self::VERBS, ['pak', 'bu', 'nih', 'bagaimana', 'caranya', 'gimana', 'kah', 'mana', 'kemana', 'ke']);
        if (! $subject) {
            return 'Bisa, Bapak/Ibu. ' . self::ASK . "\n"
                . "1. Jalan, jembatan, atau fasilitas umum rusak\n"
                . "2. Pelayanan publik (lambat, dipersulit, tidak ramah)\n"
                . "3. Penyalahgunaan wewenang atau pungutan liar (pungli)\n"
                . "4. Lainnya\n"
                . $prepare;
        }

        return 'Bisa, Bapak/Ibu. Pengaduan pelayanan publik dapat disampaikan lewat ' . self::LAPOR . '. Simpan nomor tiket untuk memantau tindak lanjutnya.'
            . ' Bila menyangkut layanan informasi publik, Bapak/Ibu bisa menghubungi PPID lewat halaman Kontak (' . route('contact.index') . '); dugaan maladministrasi juga dapat diadukan ke ' . self::OMBUDSMAN . '. '
            . $prepare . $more;
    }

    private static function hasVerb(string $text): bool
    {
        return (bool) preg_match('/\b(' . implode('|', self::VERBS) . ')\b/u', $text);
    }
}
