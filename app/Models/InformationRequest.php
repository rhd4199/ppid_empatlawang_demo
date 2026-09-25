<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InformationRequest extends Model
{
    protected $table = 'forms_requests';
    protected $guarded = [];

    protected $appends = ['ktp_file_url'];

    public function getKtpFileUrlAttribute(): ?string
    {
        return storage_url($this->ktp_file);
    }
}
