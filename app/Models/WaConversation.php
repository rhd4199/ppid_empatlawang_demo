<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WaConversation extends Model
{
    public const STATUSES = ['new' => 'Baru', 'read' => 'Dibaca', 'done' => 'Selesai'];

    protected $guarded = [];

    protected $casts = ['last_message_at' => 'datetime'];

    public function messages(): HasMany
    {
        return $this->hasMany(WaMessage::class);
    }
}
