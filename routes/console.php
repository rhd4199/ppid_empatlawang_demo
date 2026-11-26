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

Artisan::command('storage:push-public {--delete-local : hapus salinan lokal setelah terunggah}', function () {
    $disk = \Illuminate\Support\Facades\Storage::disk('public');
    if (config('filesystems.disks.public.driver') === 'local') {
        $this->error('Disk "public" masih lokal: isi CLOUDFLARE_R2_* (atau S3_*) di .env dulu.');

        return 1;
    }

    $root = storage_path('app/public');
    $ok = $failed = 0;
    foreach (\Illuminate\Support\Facades\File::allFiles($root) as $file) {
        $path = str_replace(DIRECTORY_SEPARATOR, '/', $file->getRelativePathname());
        if (str_starts_with(basename($path), '.')) {
            continue;
        }
        $stream = fopen($file->getPathname(), 'rb');
        $put = $disk->put($path, $stream);
        is_resource($stream) && fclose($stream);
        if ($put) {
            $ok++;
            $this->option('delete-local') && unlink($file->getPathname());
        } else {
            $failed++;
            $this->error("gagal: {$path}");
        }
    }
    $this->info("terunggah: {$ok}, gagal: {$failed}");

    return $failed ? 1 : 0;
})->purpose('Unggah isi storage/app/public ke disk "public" (R2/S3)');
