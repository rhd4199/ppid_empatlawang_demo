<?php

namespace App\Support;

use App\Models\ServiceRequirement;

/**
 * OpenAI tools for Sapa Madani: the model looks up the Persyaratan Layanan data (/persyaratan) itself
 * (list, search, detail) instead of relying only on the rows pre-matched into the system prompt.
 * Read-only, published rows only.
 */
class AssistantTools
{
    /** Tool rounds per answer (each round = one more OpenAI call). */
    public const MAX_ROUNDS = 3;

    /** OpenAI "tools" parameter. */
    public static function definitions(): array
    {
        $fn = fn (string $name, string $description, array $properties = [], array $required = []) => ['type' => 'function', 'function' => [
            'name' => $name,
            'description' => $description,
            'parameters' => ['type' => 'object', 'properties' => (object) $properties, 'required' => $required],
        ]];

        return [
            $fn('daftar_persyaratan', 'Daftar semua layanan yang persyaratannya tersedia di halaman Persyaratan Layanan (KTP-el, KK, akta, SIM, STNK, tanah, izin usaha, dll.), per kategori. Pakai bila pengguna bertanya layanan apa saja yang ada, atau untuk mencari slug yang tepat.',
                ['kategori' => ['type' => 'string', 'enum' => array_keys(ServiceRequirement::CATEGORIES), 'description' => 'Opsional: hanya satu kategori.']]),
            $fn('cari_persyaratan', 'Cari layanan di data Persyaratan Layanan dengan kata kunci (mis. "ktp hilang", "balik nama motor", "pirt"). Mengembalikan judul, instansi, slug dan tautan.',
                ['kata_kunci' => ['type' => 'string']], ['kata_kunci']),
            $fn('detail_persyaratan', 'Rincian satu layanan: persyaratan, langkah, biaya, lama proses, dasar hukum, instansi, tautan. Pakai sebelum menjawab persyaratan sebuah layanan.',
                ['slug' => ['type' => 'string', 'description' => 'slug dari daftar_persyaratan atau cari_persyaratan']], ['slug']),
        ];
    }

    /** Runs one tool call; the result goes back to the model as JSON. */
    public static function run(string $name, array $args): array
    {
        $published = fn () => ServiceRequirement::where('is_published', true);
        $brief = fn ($r) => ['judul' => $r->title, 'slug' => $r->slug, 'instansi' => $r->agency, 'tautan' => route('service-requirements.show', $r->slug)];

        return match ($name) {
            'daftar_persyaratan' => $published()
                ->when(isset(ServiceRequirement::CATEGORIES[$args['kategori'] ?? '']), fn ($q) => $q->where('category', $args['kategori']))
                ->orderBy('category')->orderBy('title')->get()
                ->groupBy(fn ($r) => ServiceRequirement::CATEGORIES[$r->category] ?? $r->category)
                ->map(fn ($rows) => $rows->map(fn ($r) => ['judul' => $r->title, 'slug' => $r->slug])->values())
                ->all() + ['halaman' => route('service-requirements.index')],
            // "syarat " = explicit intent, so find() does not require the service name in the title
            'cari_persyaratan' => ['hasil' => RequirementFinder::find('syarat ' . mb_substr((string) ($args['kata_kunci'] ?? ''), 0, 200))
                ->map($brief)->values()->all() ?: 'Tidak ada yang cocok. Coba kata lain atau daftar_persyaratan.'],
            'detail_persyaratan' => ($r = $published()->where('slug', (string) ($args['slug'] ?? ''))->first())
                ? $brief($r) + array_filter([
                    'persyaratan' => RequirementFinder::items($r->requirements),
                    'langkah' => $r->steps ? RequirementFinder::items($r->steps) : null,
                    'biaya' => $r->cost,
                    'lama_proses' => $r->duration,
                    'dasar_hukum' => $r->legal_basis ? plain_text($r->legal_basis) : null,
                    'dicek_pada' => $r->checked_at?->format('d-m-Y'),
                ])
                : ['error' => 'Slug tidak ditemukan. Pakai cari_persyaratan atau daftar_persyaratan.'],
            default => ['error' => "Tool {$name} tidak ada."],
        };
    }
}
