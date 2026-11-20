<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AssistantController extends Controller
{
    private const FAILED = 'Maaf, Sapa Madani sedang tidak dapat menjawab. Silakan coba lagi nanti.';

    private const SYSTEM_PROMPT = 'Anda adalah Sapa Madani, asisten virtual PPID (Pejabat Pengelola Informasi dan Dokumentasi) '
        . 'Kabupaten Empat Lawang. Jawab dalam Bahasa Indonesia yang ramah, sopan dan singkat. Bantu warga soal keterbukaan '
        . 'informasi publik: cara mengajukan permohonan informasi (menu Permohonan), keberatan, informasi berkala/serta-merta/'
        . 'setiap saat, dan kontak (halaman Kontak). Jangan mengarang data, angka, nama pejabat atau tanggal; bila tidak tahu, '
        . 'sarankan mengajukan permohonan informasi atau menghubungi PPID. Tolak pertanyaan di luar layanan pemerintahan dengan sopan.';

    // Order: OPENAI_API_KEY set → OpenAI Chat Completions; else assistant_api_url (forwarded as {"message"}, must answer
    // {"reply"}); else a fixed reply. ponytail: single-turn (no chat history sent), cost bounded by max_tokens + route throttle.
    public function chat(Request $request)
    {
        $data = $request->validate(['message' => 'required|string|max:1000']);
        $settings = assistant_settings();

        if (! $settings['enabled']) {
            abort(404);
        }

        if (config('services.openai.key')) {
            return $this->reply(fn () => Http::withToken(config('services.openai.key'))->timeout(30)->acceptJson()
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => config('services.openai.model'),
                    'max_tokens' => (int) config('services.openai.max_tokens'),
                    'messages' => [
                        ['role' => 'system', 'content' => self::SYSTEM_PROMPT],
                        ['role' => 'user', 'content' => $data['message']],
                    ],
                ])->throw()->json('choices.0.message.content'), 'OpenAI');
        }

        if ($settings['api_url'] !== '') {
            return $this->reply(fn () => Http::timeout(20)->acceptJson()
                ->post($settings['api_url'], ['message' => $data['message']])->throw()->json('reply'), 'API');
        }

        return response()->json(['reply' => 'Terima kasih, pesan Anda sudah kami terima. Sapa Madani sedang disiapkan — untuk sementara silakan ajukan permohonan informasi melalui menu Permohonan atau hubungi kami di halaman Kontak.']);
    }

    private function reply(callable $call, string $source)
    {
        try {
            $reply = $call();
        } catch (\Throwable $e) {
            // Exception messages hold the URL and response body only, never the Authorization header.
            Log::warning("Sapa Madani {$source} error: " . class_basename($e) . ' ' . $e->getCode() . ' ' . mb_substr($e->getMessage(), 0, 300));
            $reply = null;
        }

        return response()->json(['reply' => is_string($reply) && trim($reply) !== '' ? trim($reply) : self::FAILED]);
    }
}
