<?php

namespace App\Support;

use App\Models\WaMessage;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

/**
 * One handler for Larika WhatsApp API events, used by the GET /v1/events poller and the webhook alike.
 * Event: {id, channel, event, instanceId, ts, data} (https://gateway.larika.id/docs.md, Events / Webhook).
 * Delivery is at least once → each event id is handled once (larika_events).
 */
class LarikaEvents
{
    /** Outgoing statuses in order; an older status arriving late never moves a message back. */
    private const RANK = ['pending' => 0, 'sent' => 1, 'delivered' => 2, 'read' => 3];

    /**
     * @return array<int, \App\Models\WaConversation> conversations with a new citizen message (for WhatsAppBot::handle)
     */
    public static function handle(array $event): array
    {
        $id = $event['id'] ?? null;
        if (! is_scalar($id) || $id === '' || DB::table('larika_events')->where('event_id', (string) $id)->exists()) {
            return [];
        }
        $type = is_string($event['event'] ?? null) ? $event['event'] : '';
        $data = is_array($event['data'] ?? null) ? $event['data'] : [];

        $conversations = match ($type) {
            'message.incoming' => WhatsAppInbox::store(WhatsAppInbox::parse('larika', $event)),
            'message.sent', 'message.status' => self::updateOutgoing($data, strtolower((string) ($data['status'] ?? 'sent'))),
            'message.failed', 'message.expired' => self::updateOutgoing($data, 'failed', $type === 'message.expired' ? 'Kedaluwarsa sebelum terkirim' : null),
            default => [], // other events: nothing to do, still recorded as handled
        };

        // Recorded after handling: an event that crashed is handled again on the next delivery. No transaction: a failed
        // unique insert aborts a Postgres transaction (same as WhatsAppInbox::store).
        try {
            DB::table('larika_events')->insert(['event_id' => (string) $id, 'event' => mb_substr($type, 0, 64), 'handled_at' => now()]);
        } catch (UniqueConstraintViolationException) {
            return []; // handled in parallel by the other channel
        }

        return $conversations;
    }

    /** Our outgoing rows carry ref "wa-out-<wa_messages.id>" (WhatsAppGateway::sendLogged). */
    private static function updateOutgoing(array $data, string $status, ?string $error = null): array
    {
        if (! preg_match('/^wa-out-(\d+)$/', (string) ($data['ref'] ?? ''), $m)) {
            return [];
        }
        $message = WaMessage::where('direction', 'out')->find((int) $m[1]);
        if (! $message) {
            return [];
        }

        if ($status === 'failed') {
            $reason = $error ?? (is_string($data['error'] ?? null) ? $data['error'] : 'Gagal terkirim');
            $message->update(['status' => 'failed', 'error' => mb_substr('Larika: ' . $reason, 0, 500)]);
            // A reply that was marked sent did not arrive after all: back to the officer queue.
            $message->conversation->update(['status' => 'new', 'handoff_reason' => 'Balasan gagal terkirim']);
        } elseif (isset(self::RANK[$status]) && $message->status !== 'failed' && self::RANK[$status] > (self::RANK[$message->status] ?? 0)) {
            $message->update(['status' => $status]);
        }

        return [];
    }
}
