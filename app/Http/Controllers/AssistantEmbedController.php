<?php

namespace App\Http\Controllers;

/**
 * Sapa Madani on other sites (empatlawangkab.go.id, OPD sites): one <script src=".../sapa-madani/embed.js"> adds
 * the floating button and opens /sapa-madani/embed (the same chat panel as this site) in an iframe.
 * Only origins in ASSISTANT_EMBED_ORIGINS may frame it (CSP frame-ancestors). The chat POST is CSRF-exempt
 * (bootstrap/app.php): a cross-site iframe gets no session cookie; it stays rate-limited (throttle:assistant).
 */
class AssistantEmbedController extends Controller
{
    public function page()
    {
        $assistant = assistant_settings();
        abort_unless($assistant['enabled'], 404);

        return response()->view('assistant-embed', ['assistant' => $assistant, 'waUrl' => $assistant['wa_url']])
            ->header('Content-Security-Policy', 'frame-ancestors ' . config('services.assistant.embed_origins'));
    }

    public function script()
    {
        abort_unless(assistant_settings()['enabled'], 404);

        return response()->view('assistant-embed-js')
            ->header('Content-Type', 'application/javascript; charset=utf-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }
}
