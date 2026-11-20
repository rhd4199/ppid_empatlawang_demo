<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('mail:test {to}', function (string $to) {
    if (! filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $this->error("Alamat email tidak valid: {$to}");

        return 1;
    }

    try {
        Mail::raw('Email uji dari '.config('app.name').' ('.config('app.url').'). Konfigurasi email berjalan.', function ($message) use ($to) {
            $message->to($to)->subject('Email uji - '.config('mail.from.name'));
        });
    } catch (\Throwable $e) {
        $this->error('Gagal mengirim: '.$e->getMessage());

        return 1;
    }

    $this->info('Terkirim ke '.$to.' lewat mailer "'.config('mail.default').'".');

    return 0;
})->purpose('Send a test email to check the mail (SMTP) configuration');
