<?php

namespace App\Support;

use App\Models\WaConversation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Blue ticks and "sedang mengetik…" for the citizen (Larika POST /v1/read, /v1/presence; WhatsApp Web numbers only).
 * Best effort: never throws, does nothing for other providers or without LARIKA_KEY.
 */
class LarikaPresence
{
    /** Marks the citizen's unanswered messages read; $typing also shows "sedang mengetik…" (the bot is about to answer). */
    public static function read(WaConversation $conversation, bool $typing = false): void
    {
        $key = (string) config('services.larika.key');
        if ($key === '' || WhatsAppGateway::settings()['provider'] !== 'larika') {
            return;
        }
        $base = rtrim((string) config('services.larika.url'), '/');
        $lastOut = $conversation->messages()->where('direction', 'out')->max('id') ?? 0;
        $ids = $conversation->messages()->where('direction', 'in')->where('id', '>', $lastOut)
            ->whereNotNull('provider_message_id')->latest('id')->limit(10)->pluck('provider_message_id');
        try {
            foreach ($ids as $id) {
                Http::withToken($key)->acceptJson()->timeout(8)->post("{$base}/v1/read", ['messageId' => $id]);
            }
            if ($typing) {
                Http::withToken($key)->acceptJson()->timeout(8)->post("{$base}/v1/presence", ['chat' => $conversation->phone, 'state' => 'composing']);
            }
        } catch (\Throwable $e) {
            Log::warning('Larika read/presence failed: ' . class_basename($e) . ' ' . mb_substr($e->getMessage(), 0, 150));
        }
    }
}
