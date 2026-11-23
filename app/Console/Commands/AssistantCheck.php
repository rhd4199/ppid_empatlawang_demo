<?php

namespace App\Console\Commands;

use App\Http\Controllers\AssistantController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Sapa Madani diagnosis for servers without tinker (read-only home): shows which AI the chat uses, makes one tiny
 * OpenAI call and prints its HTTP status / error, then (--full) answers "halo" through the real chat path.
 * Never prints the key itself.
 */
class AssistantCheck extends Command
{
    protected $signature = 'assistant:check {--full : also answer "halo" through the same code as the site chat (uses tools, slower)}';

    protected $description = 'Check the Sapa Madani AI setup (OpenAI key, model, connection) without showing the key';

    public function handle(): int
    {
        $key = (string) config('services.openai.key');
        $settings = assistant_settings();
        $this->line('Config ter-cache      : ' . (app()->configurationIsCached() ? 'ya (ubah env → php artisan config:clear)' : 'tidak'));
        $this->line('Chat aktif (admin)    : ' . ($settings['enabled'] ? 'ya' : 'TIDAK — Admin › Pengaturan Kontak › Sapa Madani'));
        $this->line('OPENAI_API_KEY        : ' . ($key === '' ? 'KOSONG / tidak terbaca' : 'terisi, ' . strlen($key) . ' karakter, awalan ' . substr($key, 0, 3)));
        $this->line('OPENAI_MODEL          : ' . config('services.openai.model'));
        $this->line('OPENAI_SEARCH_MODEL   : ' . (config('services.openai.search_model') ?: '(kosong, cari_web mati)'));
        $this->line('assistant_api_url     : ' . ($settings['api_url'] !== '' ? 'terisi (dipakai bila key kosong)' : 'kosong'));

        if ($key === '') {
            $this->error('Key tidak terbaca: chat menjawab "Sapa Madani sedang tidak aktif". Cek nama OPENAI_API_KEY di .env folder aplikasi ini, lalu php artisan config:clear.');

            return self::FAILURE;
        }
        if (trim($key) !== $key || str_contains($key, '"') || str_contains($key, "'")) {
            $this->warn('Key mengandung spasi atau tanda kutip — periksa baris OPENAI_API_KEY di .env.');
        }

        try {
            $res = Http::withToken($key)->timeout(30)->acceptJson()->post('https://api.openai.com/v1/chat/completions', [
                'model' => config('services.openai.model'),
                'max_tokens' => 5,
                'messages' => [['role' => 'user', 'content' => 'Balas: ok']],
            ]);
        } catch (Throwable $e) {
            $this->error('Tidak bisa menghubungi api.openai.com: ' . class_basename($e) . ' ' . mb_substr($e->getMessage(), 0, 300));
            $this->line('cURL 6/7/28 = jaringan/firewall/proxy keluar server; cURL 60/77 = sertifikat CA PHP (curl.cainfo).');

            return self::FAILURE;
        }
        if (! $res->successful()) {
            $this->error("OpenAI menolak (HTTP {$res->status()}): " . ($res->json('error.code') ?? '') . ' ' . mb_substr((string) ($res->json('error.message') ?? $res->body()), 0, 300));
            $this->line('401 = key salah/dicabut; 429 insufficient_quota = saldo/billing habis; 404 model_not_found = OPENAI_MODEL salah / tak tersedia di akun.');

            return self::FAILURE;
        }
        $this->info('OpenAI OK (HTTP 200), model ' . $res->json('model') . '.');

        if ($this->option('full')) {
            $started = microtime(true);
            $reply = app(AssistantController::class)->answer('halo');
            $this->line(sprintf('Jawaban chat (%.1f dtk): %s', microtime(true) - $started, $reply));
            $this->line('Gagal di sini tapi tes di atas OK → lihat storage/logs/laravel.log baris "Sapa Madani OpenAI error".');
        }

        return self::SUCCESS;
    }
}
