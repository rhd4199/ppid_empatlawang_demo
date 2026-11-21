<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaMessage extends Model
{
    protected $guarded = [];

    protected $casts = ['sent_at' => 'datetime'];

    /** Outgoing status → WhatsApp tick (pending clock, sent ✓, delivered ✓✓ grey, read ✓✓ blue, failed !). */
    public const TICKS = [
        'pending' => ['fa-regular fa-clock', 'Menunggu'],
        'sent' => ['fas fa-check', 'Terkirim'],
        'delivered' => ['fas fa-check-double', 'Diterima'],
        'read' => ['fas fa-check-double', 'Dibaca'],
        'failed' => ['fas fa-circle-exclamation', 'Gagal terkirim'],
    ];

    /** Day separator label (WIB), like WhatsApp: Hari ini / Kemarin / 21 November 2026. */
    public static function dayLabel($date): string
    {
        $date = $date->copy()->timezone('Asia/Jakarta');
        $today = now('Asia/Jakarta');

        return $date->isSameDay($today) ? 'Hari ini' : ($date->isSameDay($today->copy()->subDay()) ? 'Kemarin' : $date->locale('id')->translatedFormat('j F Y'));
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(WaConversation::class, 'wa_conversation_id');
    }
}
