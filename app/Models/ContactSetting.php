<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactSetting extends Model
{
    protected $guarded = [];

    protected $casts = [
        'phones' => 'array',
        'emails' => 'array',
        'working_hours' => 'array',
        'social_media' => 'array',
    ];
}
