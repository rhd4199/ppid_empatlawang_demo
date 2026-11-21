<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Throwable;

/** Connection status of the Larika WhatsApp API for the settings page: key, number (GET /v1/number), poller, last event. */
class LarikaStatus
{
    /** Set by the poller after every answered poll (whatsapp:larika-poll). */
    public const POLL_KEY = 'larika_poll_at';

    /** Poll holds up to 25 s; older than this = poller not running. */
    private const POLL_STALE_SECONDS = 90;

    /**
     * @return array{state: 'missing'|'rejected'|'error'|'ok', error: ?string, number: ?array, poller: bool, last_poll_at: ?Carbon, last_event_at: ?Carbon}
     */
    public static function check(): array
    {
        $key = (string) config('services.larika.key');
        [$state, $error, $number] = $key === '' ? ['missing', null, null] : self::number($key);
        $lastPoll = Cache::get(self::POLL_KEY);
        $lastPoll = $lastPoll ? Carbon::parse($lastPoll) : null;
        $lastEvent = DB::table('larika_events')->max('handled_at');

        return [
            'state' => $state,
            'error' => $error,
            'number' => $number,
            'poller' => $lastPoll !== null && $lastPoll->gt(now()->subSeconds(self::POLL_STALE_SECONDS)),
            'last_poll_at' => $lastPoll,
            'last_event_at' => $lastEvent ? Carbon::parse($lastEvent) : null,
        ];
    }

    private static function number(string $key): array
    {
        try {
            $res = Http::withToken($key)->acceptJson()->timeout(5)
                ->get(rtrim((string) config('services.larika.url'), '/') . '/v1/number');
        } catch (Throwable $e) {
            return ['error', 'Gateway tidak bisa dihubungi (' . class_basename($e) . ').', null];
        }
        if (in_array($res->status(), [401, 403], true)) {
            return ['rejected', "Key ditolak gateway (HTTP {$res->status()}).", null];
        }
        if (! $res->successful()) {
            return ['error', "Gateway menjawab HTTP {$res->status()}.", null];
        }

        return ['ok', null, [
            'name' => $res->json('name'),
            'status' => (string) $res->json('status'),
            'phone' => $res->json('phone'),
            'profile' => $res->json('profileName'),
            'cap_used' => $res->json('usage.capUsed'),
            'daily_cap' => $res->json('usage.dailyCap'),
            'risk' => $res->json('health.risk'),
        ]];
    }
}
