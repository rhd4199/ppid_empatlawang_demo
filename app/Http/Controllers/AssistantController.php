<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AssistantController extends Controller
{
    // ponytail: until the assistant API exists this answers with a fixed reply. Once assistant_api_url is set,
    // the message is forwarded as {"message": "..."} and the API must answer {"reply": "..."}; adjust here if its contract differs.
    public function chat(Request $request)
    {
        $data = $request->validate(['message' => 'required|string|max:1000']);
        $settings = assistant_settings();

        if (! $settings['enabled']) {
            abort(404);
        }

        if ($settings['api_url'] === '') {
            return response()->json(['reply' => 'Terima kasih, pesan Anda sudah kami terima. Sapa Madani sedang disiapkan — untuk sementara silakan ajukan permohonan informasi melalui menu Permohonan atau hubungi kami di halaman Kontak.']);
        }

        try {
            $reply = Http::timeout(20)->acceptJson()->post($settings['api_url'], ['message' => $data['message']])->throw()->json('reply');
        } catch (\Throwable $e) {
            Log::warning('Sapa Madani API error: ' . $e->getMessage());
            $reply = null;
        }

        return response()->json(['reply' => is_string($reply) && $reply !== '' ? $reply : 'Maaf, Sapa Madani sedang tidak dapat menjawab. Silakan coba lagi nanti.']);
    }
}
