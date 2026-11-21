<?php

namespace App\Support;

use App\Models\PpidSetting;
use App\Models\WaConversation;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

/**
 * Sends WhatsApp text messages through the provider set in Admin → WhatsApp Gateway.
 * Settings live in ppid_settings (keys wa_*); the token is stored encrypted and never shown again.
 */
class WhatsAppGateway
{
    public const PROVIDERS = [
        'larika' => 'Larika WhatsApp API',
        'fonnte' => 'Fonnte',
        'wablas' => 'Wablas',
        'cloud' => 'WhatsApp Cloud API (Meta)',
        'waha' => 'WAHA (server sendiri)',
    ];

    /** Used when the API URL field is empty. Wablas/WAHA have no fixed host. */
    public const DEFAULT_URLS = [
        'fonnte' => 'https://api.fonnte.com',
        'cloud' => 'https://graph.facebook.com/v21.0',
    ];

    private const KEYS = ['wa_enabled', 'wa_auto_reply', 'wa_provider', 'wa_api_url', 'wa_token', 'wa_sender'];

    public static function settings(): array
    {
        $v = PpidSetting::whereIn('key', self::KEYS)->pluck('value', 'key');

        return [
            'enabled' => ($v['wa_enabled'] ?? '0') === '1',
            // off by default: replies go out only via "Setujui & kirim" until an admin turns the bot on
            'auto_reply' => ($v['wa_auto_reply'] ?? '0') === '1',
            'provider' => $v['wa_provider'] ?? 'fonnte',
            'api_url' => $v['wa_api_url'] ?? '',
            'sender' => $v['wa_sender'] ?? '',
            'has_token' => ($v['wa_token'] ?? '') !== '',
        ];
    }

    /** $token null = keep the stored one, '' = remove it. */
    public static function save(array $data, ?string $token): void
    {
        $values = [
            'wa_enabled' => !empty($data['enabled']) ? '1' : '0',
            'wa_auto_reply' => !empty($data['auto_reply']) ? '1' : '0',
            'wa_provider' => $data['provider'],
            'wa_api_url' => rtrim(trim($data['api_url'] ?? ''), '/'),
            'wa_sender' => trim($data['sender'] ?? ''),
        ];
        if ($token !== null) {
            $values['wa_token'] = $token === '' ? '' : Crypt::encryptString($token);
        }
        foreach ($values as $key => $value) {
            PpidSetting::updateOrCreate(['key' => $key], ['value' => $value, 'description' => 'WhatsApp Gateway']);
        }
    }

    /** Shared secret in the webhook URL (?secret=…); created on first use. Also the Meta verify token. */
    public static function webhookSecret(): string
    {
        return PpidSetting::firstOrCreate(
            ['key' => 'wa_webhook_secret'],
            ['value' => Str::random(40), 'description' => 'WhatsApp Gateway']
        )->value;
    }

    /** "0812-3456 789" / "+62812…" → "62812…"; '' when too short. */
    public static function normalize(?string $phone): string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);
        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        }

        return strlen($digits) >= 9 ? $digits : '';
    }

    /**
     * Never throws, so a failing provider cannot break the caller's request.
     * $force sends even when the gateway is switched off (admin test button).
     *
     * @return array{ok: bool, error: ?string}
     */
    public static function send(?string $phone, string $text, bool $force = false, ?string $ref = null): array
    {
        $s = self::settings();
        $to = self::normalize($phone);
        $larika = $s['provider'] === 'larika';
        $token = $larika ? (string) config('services.larika.key') : self::token();
        $base = $larika ? rtrim((string) config('services.larika.url'), '/')
            : ($s['api_url'] !== '' ? $s['api_url'] : (self::DEFAULT_URLS[$s['provider']] ?? ''));

        $error = match (true) {
            !$s['enabled'] && !$force => 'WhatsApp Gateway tidak aktif.',
            !isset(self::PROVIDERS[$s['provider']]) => 'Provider tidak dikenal.',
            $to === '' => 'Nomor WhatsApp tujuan tidak valid.',
            $larika && $token === '' => 'LARIKA_KEY belum diisi di env server.',
            $token === '' => 'Token/API key belum diisi.',
            $base === '' => 'URL API belum diisi.',
            $s['provider'] === 'cloud' && $s['sender'] === '' => 'Phone Number ID (pengirim) belum diisi.',
            default => null,
        };
        if ($error) {
            return ['ok' => false, 'error' => $error];
        }

        try {
            $http = Http::timeout(15)->acceptJson();
            $res = match ($s['provider']) {
                // Same ref again returns the existing message instead of sending twice (safe retries / double clicks).
                'larika' => $http->withToken($token)->post("$base/v1/messages", [
                    'to' => $to, 'text' => $text, 'ref' => $ref ?? 'ppid-' . Str::uuid(),
                ]),
                'fonnte' => $http->withHeaders(['Authorization' => $token])->asForm()
                    ->post("$base/send", ['target' => $to, 'message' => $text, 'countryCode' => '62']),
                'wablas' => $http->withHeaders(['Authorization' => $token])->asForm()
                    ->post("$base/api/send-message", ['phone' => $to, 'message' => $text]),
                'cloud' => $http->withToken($token)->post("$base/{$s['sender']}/messages", [
                    'messaging_product' => 'whatsapp', 'to' => $to, 'type' => 'text', 'text' => ['body' => $text],
                ]),
                'waha' => $http->withHeaders(['X-Api-Key' => $token])->post("$base/api/sendText", [
                    'session' => 'default', 'chatId' => "$to@c.us", 'text' => $text,
                ]),
            };
        } catch (Throwable $e) {
            return ['ok' => false, 'error' => 'Gagal menghubungi provider: ' . $e->getMessage()];
        }

        if ($larika) {
            return self::larikaResult($res);
        }
        // Fonnte/Wablas answer HTTP 200 with {"status": false, "reason"/"message": ...} on failure.
        if ($res->successful() && $res->json('status') !== false) {
            return ['ok' => true, 'error' => null];
        }
        $reason = $res->json('reason') ?? $res->json('error.message') ?? $res->json('message') ?? 'HTTP ' . $res->status();

        return ['ok' => false, 'error' => 'Provider menolak: ' . (is_string($reason) ? $reason : json_encode($reason))];
    }

    /**
     * Sends a reply in a conversation and stores it as an outgoing wa_messages row (sent / failed + error).
     * The row is created first so its id is the provider ref ("wa-out-<id>").
     *
     * @return array{ok: bool, error: ?string, message: \App\Models\WaMessage}
     */
    public static function sendLogged(WaConversation $conversation, string $text, ?string $sender = null): array
    {
        $message = $conversation->messages()->create([
            'direction' => 'out', 'sender' => $sender, 'body' => $text, 'sent_at' => now(), 'status' => 'pending',
        ]);
        $result = self::send($conversation->phone, $text, ref: 'wa-out-' . $message->id);
        $message->update(['status' => $result['ok'] ? 'sent' : 'failed', 'error' => $result['error']]);

        return $result + ['message' => $message];
    }

    /** 202 queued / 200 sent = ok; 429 daily cap = not queued, report when to retry (never retried here); else the API's error. */
    private static function larikaResult(\Illuminate\Http\Client\Response $res): array
    {
        if (in_array($res->status(), [200, 202], true)) {
            return ['ok' => true, 'error' => null];
        }
        if ($res->status() === 429) {
            $when = $res->json('nextSlotAt');
            $when = $when ? Carbon::parse($when)->timezone('Asia/Jakarta')->format('d/m/Y H:i') . ' WIB'
                : ($res->header('Retry-After') !== '' ? $res->header('Retry-After') . ' detik lagi' : null);

            return ['ok' => false, 'error' => 'Batas kirim harian nomor WhatsApp tercapai (' . ($res->json('error') ?? 'daily_cap_reached') . ')'
                . ($when ? ", coba lagi setelah {$when}." : '.')];
        }
        $reason = $res->json('error') ?? $res->json('message') ?? 'HTTP ' . $res->status();

        return ['ok' => false, 'error' => 'Provider menolak: ' . (is_string($reason) ? $reason : json_encode($reason))];
    }

    private static function token(): string
    {
        $raw = PpidSetting::where('key', 'wa_token')->value('value') ?? '';
        if ($raw === '') {
            return '';
        }
        try {
            return Crypt::decryptString($raw);
        } catch (DecryptException) {
            return ''; // APP_KEY changed → admin must re-enter the token
        }
    }
}
