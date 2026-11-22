<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Official extends Model
{
    /** Sections on /profil-pejabat, in this order. */
    public const GROUPS = [
        'pemkab' => 'Pemerintah Kabupaten',
        'forkopimda' => 'Forkopimda',
        'dprd' => 'DPRD Kabupaten',
        'kecamatan' => 'Camat',
    ];

    protected $guarded = [];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    protected $appends = ['photo_url'];

    /** Sort by GROUPS order (pemkab first), then by `order`. */
    public function scopeInGroupOrder($query)
    {
        $cases = collect(array_keys(self::GROUPS))->map(fn ($g, $i) => "WHEN '{$g}' THEN {$i}")->implode(' ');

        // `group` is a reserved word: quote it the way the connection does (MySQL backticks, pg/sqlite double quotes).
        $column = $query->getQuery()->getGrammar()->wrap('group');

        return $query->orderByRaw("CASE {$column} {$cases} ELSE 99 END");
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return storage_url($this->photo);
    }
}
