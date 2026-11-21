<?php

namespace App\Support;

use App\Models\WaConversation;
use App\Models\WaMessage;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;

/** Turns provider webhook payloads into wa_conversations / wa_messages rows. */
class WhatsAppInbox
{
    /**
     * Incoming citizen text messages in a provider payload (own messages, groups and status updates skipped).
     *
     * @return list<array{id: ?string, phone: string, name: ?string, text: string, time: ?int}>
     */
    public static function parse(string $provider, array $p): array
    {
        $rows = match ($provider) {
            // Fonnte: flat form/JSON {sender, name, message, member (group), inboxid}
            'fonnte' => empty($p['member']) ? [[
                'id' => $p['inboxid'] ?? $p['id'] ?? null, 'phone' => $p['sender'] ?? '',
                'name' => $p['name'] ?? null, 'text' => $p['message'] ?? $p['pesan'] ?? '', 'time' => $p['timestamp'] ?? null,
            ]] : [],
            // Wablas: {id, phone, pushName, message, isGroup, isFromMe, timestamp}
            'wablas' => empty($p['isGroup']) && empty($p['isFromMe']) ? [[
                'id' => $p['id'] ?? null, 'phone' => $p['phone'] ?? '',
                'name' => $p['pushName'] ?? null, 'text' => $p['message'] ?? '', 'time' => $p['timestamp'] ?? null,
            ]] : [],
            'cloud' => self::parseCloud($p),
            // Larika event {id, event, data: {phone, pushName, message, waMessageId, timestamp (ms), fromMe, isGroup}}
            'larika' => ($p['event'] ?? '') === 'message.incoming' && empty($p['data']['fromMe']) && empty($p['data']['isGroup']) ? [[
                'id' => $p['data']['waMessageId'] ?? null, 'phone' => $p['data']['phone'] ?? '',
                'name' => $p['data']['pushName'] ?? null, 'text' => $p['data']['message'] ?? '',
                'time' => is_numeric($p['data']['timestamp'] ?? null) ? intdiv((int) $p['data']['timestamp'], 1000) : null,
            ]] : [],
            // WAHA: {event: "message", payload: {id, from: "62…@c.us", body, fromMe, _data.notifyName}}
            'waha' => ($p['event'] ?? '') === 'message' && empty($p['payload']['fromMe'])
                && str_ends_with($p['payload']['from'] ?? '', '@c.us') ? [[
                'id' => $p['payload']['id'] ?? null, 'phone' => $p['payload']['from'],
                'name' => $p['payload']['_data']['notifyName'] ?? null, 'text' => $p['payload']['body'] ?? '',
                'time' => $p['payload']['timestamp'] ?? null,
            ]] : [],
            default => [],
        };

        $out = [];
        foreach ($rows as $r) {
            $phone = WhatsAppGateway::normalize(is_string($r['phone']) ? $r['phone'] : '');
            $text = is_string($r['text']) ? trim($r['text']) : '';
            if ($phone === '' || $text === '') {
                continue;
            }
            $out[] = [
                'id' => is_scalar($r['id']) && $r['id'] !== '' ? mb_substr((string) $r['id'], 0, 191) : null,
                'phone' => $phone,
                'name' => is_string($r['name']) && $r['name'] !== '' ? mb_substr($r['name'], 0, 255) : null,
                'text' => mb_substr($text, 0, 10000),
                'time' => is_numeric($r['time']) ? (int) $r['time'] : null,
            ];
        }

        return $out;
    }

    /** Meta: entry[].changes[].value.{contacts[], messages[{from, id, timestamp, type, text.body}]} */
    private static function parseCloud(array $p): array
    {
        $rows = [];
        foreach ((array) ($p['entry'] ?? []) as $entry) {
            foreach ((array) ($entry['changes'] ?? []) as $change) {
                $value = $change['value'] ?? [];
                $names = [];
                foreach ((array) ($value['contacts'] ?? []) as $c) {
                    $names[$c['wa_id'] ?? ''] = $c['profile']['name'] ?? null;
                }
                foreach ((array) ($value['messages'] ?? []) as $m) {
                    if (($m['type'] ?? '') !== 'text') {
                        continue;
                    }
                    $rows[] = [
                        'id' => $m['id'] ?? null, 'phone' => $m['from'] ?? '', 'name' => $names[$m['from'] ?? ''] ?? null,
                        'text' => $m['text']['body'] ?? '', 'time' => $m['timestamp'] ?? null,
                    ];
                }
            }
        }

        return $rows;
    }

    /** Stores parsed messages (duplicate provider ids skipped); returns the conversations that got a new message, keyed by id. */
    public static function store(array $messages): array
    {
        $stored = [];
        foreach ($messages as $m) {
            if ($m['id'] !== null && WaMessage::where('provider_message_id', $m['id'])->exists()) {
                continue;
            }
            $at = $m['time'] ? Carbon::createFromTimestamp($m['time']) : now();
            // No transaction: on Postgres a failed unique insert would abort it. createOrFirst survives a parallel first message.
            $conversation = WaConversation::createOrFirst(['phone' => $m['phone']]);
            try {
                $conversation->messages()->create([
                    'direction' => 'in', 'body' => $m['text'], 'status' => 'received',
                    'provider_message_id' => $m['id'], 'sent_at' => $at,
                ]);
            } catch (UniqueConstraintViolationException) {
                continue; // same id delivered twice at once by a provider retry
            }
            // A new message reopens a finished conversation; the old draft no longer answers it.
            $conversation->update([
                'name' => $m['name'] ?? $conversation->name,
                'status' => 'new',
                'reply_draft' => null,
                'last_message_at' => $at,
            ]);
            $stored[$conversation->id] = $conversation;
        }

        return $stored;
    }
}
