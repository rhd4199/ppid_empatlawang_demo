<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A citizen report (aduan) recorded by Sapa Madani with tool catat_laporan, followed up by officers in /admin/laporan-warga. */
class CitizenReport extends Model
{
    public const CATEGORIES = [
        'infrastruktur' => 'Jalan / jembatan / fasilitas umum rusak',
        'pelayanan' => 'Pelayanan publik',
        'pungli' => 'Pungli / penyalahgunaan wewenang',
        'lingkungan' => 'Lingkungan / sampah / kebersihan',
        'bencana' => 'Bencana / keadaan darurat',
        'sosial' => 'Bantuan sosial / kesejahteraan',
        'lainnya' => 'Lainnya',
    ];

    public const STATUSES = [
        'baru' => 'Baru',
        'diteruskan' => 'Diteruskan ke instansi',
        'diproses' => 'Sedang diproses',
        'selesai' => 'Selesai',
        'ditolak' => 'Tidak dapat ditindaklanjuti',
    ];

    protected $guarded = [];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(WaConversation::class, 'wa_conversation_id');
    }

    /** LW-<year>-<4-digit sequence>, sequence per year. */
    public static function nextCode(): string
    {
        $year = now('Asia/Jakarta')->year;
        $last = self::where('code', 'like', "LW-{$year}-%")->orderByDesc('id')->value('code');

        return sprintf('LW-%d-%04d', $year, $last ? (int) substr($last, -4) + 1 : 1);
    }
}
