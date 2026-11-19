<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;

class DocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Standar Layanan - SOP
        $this->document([
            'title' => 'SOP Pelayanan Informasi Publik',
            'category' => 'standar_layanan_sop',
            'description' => 'Standar Operasional Prosedur pelayanan informasi publik.',
            'file_path' => 'documents/sop-ppid.pdf',
            'is_published' => true
        ]);

        // Standar Layanan - Alur
        $this->document([
            'title' => 'Alur Permohonan Informasi',
            'category' => 'standar_layanan_alur',
            'description' => 'Bagan alur proses permohonan informasi publik.',
            'file_path' => 'documents/alur-permohonan.pdf',
            'is_published' => true
        ]);

        $this->document([
            'title' => 'Alur Pengajuan Keberatan',
            'category' => 'standar_layanan_alur',
            'description' => 'Bagan alur proses pengajuan keberatan informasi publik.',
            'file_path' => 'documents/alur-keberatan.pdf',
            'is_published' => true
        ]);

        // Standar Layanan - Tata Cara
        $this->document([
            'title' => 'Tata Cara Permohonan Informasi',
            'category' => 'standar_layanan_tata_cara',
            'description' => 'Panduan lengkap cara mengajukan permohonan informasi.',
            'file_path' => 'documents/tata-cara-permohonan.pdf',
            'is_published' => true
        ]);

        // Standar Layanan - Sengketa
        $this->document([
            'title' => 'Tata Cara Penyelesaian Sengketa',
            'category' => 'standar_layanan_sengketa',
            'description' => 'Prosedur penyelesaian sengketa informasi di Komisi Informasi.',
            'file_path' => 'documents/sengketa.pdf',
            'is_published' => true
        ]);

        // Standar Layanan - Maklumat
        $this->document([
            'title' => 'Maklumat Pelayanan PPID',
            'category' => 'standar_layanan_maklumat',
            'description' => 'Pernyataan kesanggupan pelayanan PPID.',
            'file_path' => 'documents/maklumat.pdf',
            'is_published' => true
        ]);

        // Standar Layanan - Biaya
        $this->document([
            'title' => 'Standar Biaya Perolehan Informasi',
            'category' => 'standar_layanan_biaya',
            'description' => 'Informasi mengenai biaya penyalinan dokumen (jika ada).',
            'file_path' => 'documents/biaya.pdf',
            'is_published' => true
        ]);

        // Standar Layanan - Permohonan (Formulir Offline)
        $this->document([
            'title' => 'Formulir Permohonan Informasi (Offline)',
            'category' => 'standar_layanan_permohonan',
            'description' => 'Unduh formulir ini jika ingin mengajukan permohonan secara langsung.',
            'file_path' => 'documents/form-permohonan.pdf',
            'is_published' => true
        ]);

        // Standar Layanan - Keberatan (Formulir Offline)
        $this->document([
            'title' => 'Formulir Pengajuan Keberatan (Offline)',
            'category' => 'standar_layanan_keberatan',
            'description' => 'Unduh formulir ini jika ingin mengajukan keberatan secara langsung.',
            'file_path' => 'documents/form-keberatan.pdf',
            'is_published' => true
        ]);

        // Laporan
        $this->document([
            'title' => 'Laporan Kinerja Instansi Pemerintah 2024',
            'category' => 'laporan_pemda',
            'description' => 'Laporan akuntabilitas kinerja instansi pemerintah tahun 2024.',
            'file_path' => 'documents/lakip-2024.pdf',
            'is_published' => true
        ]);

        $this->document([
            'title' => 'Laporan Tahunan PPID 2024',
            'category' => 'laporan_ppid',
            'description' => 'Laporan pelaksanaan layanan informasi publik tahun 2024.',
            'file_path' => 'documents/laporan-ppid-2024.pdf',
            'is_published' => true
        ]);

        $this->call(InfoPublicDocumentSeeder::class);
    }

    /** Idempotent: one row per file_path, and a placeholder PDF on the public disk if the file is missing. */
    private function document(array $attributes): void
    {
        Document::firstOrCreate(['file_path' => $attributes['file_path']], $attributes);

        if (! Storage::disk('public')->exists($attributes['file_path'])) {
            Storage::disk('public')->put($attributes['file_path'], self::placeholderPdf($attributes['title']));
        }
    }

    /**
     * One-page A4 PDF: title, optional body lines (wrapped, "## " = sub-heading) and a footer.
     * Sample data only — no layout engine, ~45 lines fit on the page.
     */
    public static function placeholderPdf(string $title, array $lines = [], string $footer = 'Dokumen contoh (placeholder) - PPID Kabupaten Empat Lawang'): string
    {
        $text = fn (string $s) => '(' . strtr(iconv('UTF-8', 'Windows-1252//TRANSLIT', $s) ?: $s, ['\\' => '\\\\', '(' => '\\(', ')' => '\\)']) . ')';
        $stream = 'BT /F2 16 Tf 60 780 Td 18 TL ' . $text($title) . " Tj /F1 11 Tf 15 TL T*\n";
        foreach ($lines as $line) {
            $heading = str_starts_with($line, '## ');
            $stream .= $heading ? "T* /F2 12 Tf\n" : '';
            foreach (explode("\n", wordwrap($heading ? substr($line, 3) : $line, 92, "\n", true)) as $part) {
                $stream .= 'T* ' . $text($part) . " Tj\n";
            }
            $stream .= $heading ? "/F1 11 Tf\n" : '';
        }
        $stream .= 'ET BT /F1 9 Tf 60 40 Td ' . $text($footer) . ' Tj ET';
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R /F2 6 0 R >> >> /Contents 5 0 R >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
            '<< /Length ' . strlen($stream) . " >>\nstream\n" . $stream . "\nendstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $i => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($i + 1) . " 0 obj\n" . $object . "\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= 'xref' . "\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";
        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        return $pdf . 'trailer << /Size ' . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n" . $xref . "\n%%EOF\n";
    }
}
