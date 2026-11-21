<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WaConversation extends Model
{
    public const STATUSES = ['new' => 'Baru', 'read' => 'Dibaca', 'done' => 'Selesai'];

    public const LABELS = ['permohonan' => 'Permohonan Informasi', 'keberatan' => 'Keberatan', 'aduan' => 'Aduan', 'umum' => 'Pertanyaan Umum'];

    protected $guarded = [];

    /** Name shown to officers: the one they typed, else the WhatsApp profile name. */
    public function displayName(): ?string
    {
        return $this->contact_name ?: $this->name;
    }

    protected $casts = ['last_message_at' => 'datetime'];

    public function messages(): HasMany
    {
        return $this->hasMany(WaMessage::class);
    }
}
