<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\LarikaEvents;
use App\Support\WhatsAppBot;
use App\Support\WhatsAppGateway;
use App\Support\WhatsAppInbox;
use Illuminate\Http\Request;

/**
 * Public endpoint the WhatsApp provider calls for incoming messages.
 * Auth: ?secret=… (or X-Webhook-Secret header) = WhatsAppGateway::webhookSecret().
 * ponytail: Meta's X-Hub-Signature-256 is not verified (needs the app secret); add it if Cloud API is chosen.
 */
class WhatsAppWebhookController extends Controller
{
    /** Meta Cloud API subscription check: echo hub.challenge when hub.verify_token matches. */
    public function verify(Request $request)
    {
        // PHP turns "hub.verify_token" into "hub_verify_token".
        abort_unless($this->validSecret($request->query('hub_verify_token') ?? $request->query('secret')), 403);

        return response((string) $request->query('hub_challenge', 'ok'), 200)->header('Content-Type', 'text/plain');
    }

    public function receive(Request $request)
    {
        abort_unless($this->validSecret($request->query('secret') ?? $request->header('X-Webhook-Secret')), 403);

        $provider = WhatsAppGateway::settings()['provider'];
        // Larika: shared event handler (dedupe on event id, outgoing status updates), same as the poller.
        $conversations = $provider === 'larika'
            ? LarikaEvents::handle($request->all())
            : WhatsAppInbox::store(WhatsAppInbox::parse($provider, $request->all()));

        // Bot reply (may wait on OpenAI + provider) after the response, so the provider's webhook call does not time out.
        // ponytail: no queue worker in this app; move to a queued job if replies get slow.
        foreach ($conversations as $conversation) {
            app()->terminating(fn () => WhatsAppBot::handle($conversation));
        }

        // Always 200 for a valid secret, otherwise providers keep retrying status/group events.
        return response()->json(['stored' => count($conversations)]);
    }

    /**
     * Larika webhook (backup of the whatsapp:larika-poll poller): same event body, same handler, so an event that
     * arrives through both is stored once. Auth: x-larika-webhook-key = LARIKA_WEBHOOK_KEY (the webhook's secret).
     * ponytail: x-larika-signature (HMAC of the body) not checked; the shared key already proves the sender.
     */
    public function larika(Request $request)
    {
        $expected = (string) config('services.larika.webhook_key');
        $given = $request->header('x-larika-webhook-key');
        if ($expected === '' || ! is_string($given) || ! hash_equals($expected, $given)) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        foreach (LarikaEvents::handle($request->all()) as $conversation) {
            app()->terminating(fn () => WhatsAppBot::handle($conversation)); // bot reply after the 200
        }

        return response()->json(['ok' => true]);
    }

    private function validSecret(mixed $given): bool
    {
        return is_string($given) && $given !== '' && hash_equals(WhatsAppGateway::webhookSecret(), $given);
    }
}
