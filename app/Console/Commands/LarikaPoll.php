<?php

namespace App\Console\Commands;

use App\Models\PpidSetting;
use App\Support\LarikaEvents;
use App\Support\LarikaStatus;
use App\Support\WhatsAppBot;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Sleep;
use Throwable;

/**
 * Long-polls Larika WhatsApp API events (GET /v1/events, https://gateway.larika.id/docs.md "Events") and feeds them to
 * LarikaEvents::handle. Runs under Supervisor/systemd (README "WhatsApp Larika"); never from a web request.
 * ponytail: no signal trapping; a stop mid-poll is harmless (cursor saved per batch, events deduped by id).
 */
class LarikaPoll extends Command
{
    protected $signature = 'whatsapp:larika-poll {--once : one poll only (tests / manual check)}';

    protected $description = 'Receive Larika WhatsApp events (incoming messages, delivery status) by long polling';

    public const CURSOR_KEY = 'larika_events_cursor';

    private const RETRY_SECONDS = 5;

    /** Exit code for a missing or rejected key: Supervisor (exitcodes=0,2) must not restart it in a loop. */
    public const AUTH_FAILED = 2;

    public function handle(): int
    {
        if (! app()->runningInConsole()) {
            return self::FAILURE; // long-running loop: never inside a web request
        }
        $key = (string) config('services.larika.key');
        if ($key === '') {
            $this->error('LARIKA_KEY belum diisi di env server.');

            return self::AUTH_FAILED; // config problem: restarting will not help
        }
        $url = rtrim((string) config('services.larika.url'), '/') . '/v1/events';

        do {
            $result = $this->pollOnce($url, $key);
            if ($result === 'auth') {
                return self::AUTH_FAILED;
            }
            if ($result === 'retry' && ! $this->option('once')) {
                Sleep::for(self::RETRY_SECONDS)->seconds();
            }
        } while (! $this->option('once'));

        return $result === 'ok' ? self::SUCCESS : self::FAILURE;
    }

    /** @return 'ok'|'retry'|'auth' */
    private function pollOnce(string $url, string $key): string
    {
        $cursor = PpidSetting::where('key', self::CURSOR_KEY)->value('value');
        try {
            // wait=25 holds the call until an event arrives; HTTP timeout must be longer than that.
            $res = Http::withToken($key)->acceptJson()->timeout(35)
                ->get($url, array_filter(['wait' => 25, 'after' => $cursor ?: null]));
        } catch (Throwable $e) {
            $this->warn('Larika events: ' . class_basename($e) . ' ' . mb_substr($e->getMessage(), 0, 200));

            return 'retry';
        }

        if (in_array($res->status(), [401, 403], true)) {
            $this->error("Larika menolak LARIKA_KEY (HTTP {$res->status()}). Periksa key di env server; poller berhenti.");

            return 'auth';
        }
        if (! $res->successful()) {
            $this->warn("Larika events: HTTP {$res->status()}, coba lagi dalam " . self::RETRY_SECONDS . ' detik.');

            return 'retry';
        }

        Cache::forever(LarikaStatus::POLL_KEY, now()->toIso8601String()); // heartbeat for the settings page

        $handled = $cursor;
        try {
            foreach ((array) $res->json('events', []) as $event) {
                if (! is_array($event)) {
                    continue;
                }
                foreach (LarikaEvents::handle($event) as $conversation) {
                    WhatsAppBot::handle($conversation);
                }
                $handled = $event['id'] ?? $handled;
            }
            $handled = $res->json('next') ?? $handled;
        } catch (Throwable $e) {
            // Resume after the last event that was fully handled; the failing one comes again on the next poll.
            Log::warning('Larika event handling failed: ' . class_basename($e) . ' ' . mb_substr($e->getMessage(), 0, 300));
            $this->warn('Larika events: gagal memproses event, coba lagi.');
            $this->saveCursor($handled);

            return 'retry';
        }
        $this->saveCursor($handled);

        return 'ok';
    }

    private function saveCursor(mixed $cursor): void
    {
        if (is_string($cursor) && $cursor !== '') {
            PpidSetting::updateOrCreate(['key' => self::CURSOR_KEY], ['value' => $cursor, 'description' => 'Larika WhatsApp: id event terakhir yang ditangani']);
        }
    }
}
