<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
        $stored = WhatsAppInbox::store(WhatsAppInbox::parse($provider, $request->all()));

        // Always 200 for a valid secret, otherwise providers keep retrying status/group events.
        return response()->json(['stored' => $stored]);
    }

    private function validSecret(mixed $given): bool
    {
        return is_string($given) && $given !== '' && hash_equals(WhatsAppGateway::webhookSecret(), $given);
    }
}
