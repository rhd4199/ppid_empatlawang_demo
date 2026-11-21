<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Registers this app's Larika webhook (POST /v1/webhooks, https://gateway.larika.id/docs.md "Webhook").
 * Same URL again only updates the events and keeps the secret, so it is safe to re-run after deploys.
 */
class LarikaWebhook extends Command
{
    protected $signature = 'whatsapp:larika-webhook {--test : send a Larika "test" event to the webhook afterwards}';

    protected $description = 'Register the Larika WhatsApp webhook (APP_URL/api/v1/whatsapp/larika) and print its secret';

    /** Events LarikaEvents::handle uses. */
    public const EVENTS = ['message.incoming', 'message.sent', 'message.status', 'message.failed', 'message.expired'];

    public function handle(): int
    {
        $key = (string) config('services.larika.key');
        if ($key === '') {
            $this->error('LARIKA_KEY belum diisi di env server.');

            return self::FAILURE;
        }
        $url = rtrim((string) config('app.url'), '/') . '/api/v1/whatsapp/larika';
        if (! str_starts_with($url, 'https://')) {
            $this->error("Larika hanya menerima webhook https://, sedangkan APP_URL = " . config('app.url') . '. Isi APP_URL dengan alamat https situs (mis. https://ppid.empatlawangkab.go.id), lalu php artisan config:clear.');

            return self::FAILURE;
        }
        $base = rtrim((string) config('services.larika.url'), '/');

        try {
            $res = Http::withToken($key)->acceptJson()->timeout(20)->post("$base/v1/webhooks", ['url' => $url, 'events' => self::EVENTS]);
        } catch (Throwable $e) {
            $this->error('Gagal menghubungi Larika: ' . class_basename($e) . ' ' . mb_substr($e->getMessage(), 0, 200));

            return self::FAILURE;
        }
        if (! $res->successful() || ! is_string($res->json('secret')) || ! is_string($res->json('id'))) {
            $this->error("Larika menolak pendaftaran (HTTP {$res->status()}): " . ($res->json('error') ?? $res->json('message') ?? mb_substr($res->body(), 0, 200)));

            return self::FAILURE;
        }

        $this->info("Webhook terdaftar: {$url} (id {$res->json('id')}).");
        $this->line('Event: ' . implode(', ', self::EVENTS));
        $this->printSecret($res->json('secret'));

        if (! $this->option('test')) {
            return self::SUCCESS;
        }

        return $this->sendTest($base, $key, $res->json('id'));
    }

    /** Shown in this terminal only (never logged); it must be copied into the server env. */
    private function printSecret(string $secret): void
    {
        $current = (string) config('services.larika.webhook_key');
        if ($current !== '' && hash_equals($secret, $current)) {
            $this->info('LARIKA_WEBHOOK_KEY di env sudah sama dengan secret webhook ini.');

            return;
        }
        $this->warn(($current === '' ? 'LARIKA_WEBHOOK_KEY belum diisi.' : 'LARIKA_WEBHOOK_KEY di env BEDA dengan secret webhook ini.')
            . ' Salin baris ini ke env server, lalu php artisan config:clear:');
        $this->line("LARIKA_WEBHOOK_KEY={$secret}");
    }

    private function sendTest(string $base, string $key, string $id): int
    {
        try {
            $res = Http::withToken($key)->acceptJson()->timeout(30)->post("$base/v1/webhooks/" . rawurlencode($id) . '/test');
        } catch (Throwable $e) {
            $this->error('Tes gagal: ' . class_basename($e));

            return self::FAILURE;
        }
        if ($res->json('ok') === true) {
            $this->info('Event tes diterima webhook (Larika menerima 2xx).');

            return self::SUCCESS;
        }
        $error = $res->json('error') ?? "HTTP {$res->status()}";
        $this->error("Event tes gagal: {$error}" . (str_contains((string) $error, '401') ? ' — LARIKA_WEBHOOK_KEY di server belum sama dengan secret di atas.' : ''));

        return self::FAILURE;
    }
}
