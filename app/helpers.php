<?php

use Illuminate\Support\Facades\Storage;

if (! function_exists('storage_url')) {
    /**
     * Resolve a public URL for a file stored on the "public" disk.
     * Disk-agnostic replacement for asset('storage/'.$path) so switching
     * disks (local <-> S3) doesn't require touching every view.
     */
    function storage_url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        // url(): local disk gives '/storage/...' → absolute on the request host; S3 URLs pass through.
        return url(Storage::disk('public')->url($path));
    }
}

if (! function_exists('social_platform')) {
    /**
     * Platform of a social_media entry, read from its URL's domain so the icon
     * is right even when the admin picked the wrong platform (or none).
     * Falls back to the stored platform, then "website".
     */
    function social_platform(array $social): string
    {
        $host = strtolower((string) parse_url(trim($social['url'] ?? ''), PHP_URL_HOST));
        $host = preg_replace('/^(www|m|web|mobile)\./', '', $host);

        $domains = [
            'instagram' => ['instagram.com', 'instagr.am'],
            'facebook' => ['facebook.com', 'fb.com', 'fb.me', 'fb.watch'],
            'youtube' => ['youtube.com', 'youtu.be'],
            'twitter' => ['twitter.com', 'x.com'],
            'tiktok' => ['tiktok.com'],
            'whatsapp' => ['wa.me', 'whatsapp.com'],
            'telegram' => ['t.me', 'telegram.me', 'telegram.org'],
            'linkedin' => ['linkedin.com'],
        ];

        foreach ($domains as $platform => $list) {
            foreach ($list as $d) {
                if ($host === $d || str_ends_with($host, '.'.$d)) {
                    return $platform;
                }
            }
        }

        return isset($domains[$social['platform'] ?? '']) ? $social['platform'] : 'website';
    }
}

if (! function_exists('social_icon')) {
    /** Font Awesome class for a social_media entry (see social_platform). */
    function social_icon(array $social): string
    {
        return [
            'instagram' => 'fab fa-instagram',
            'facebook' => 'fab fa-facebook-f',
            'youtube' => 'fab fa-youtube',
            'twitter' => 'fab fa-twitter', // ponytail: fa-x-twitter needs FA >= 6.4.2, layouts load 6.4.0
            'tiktok' => 'fab fa-tiktok',
            'whatsapp' => 'fab fa-whatsapp',
            'telegram' => 'fab fa-telegram',
            'linkedin' => 'fab fa-linkedin-in',
        ][social_platform($social)] ?? 'fas fa-globe';
    }
}

if (! function_exists('plain_text')) {
    /** Rich-text HTML as one line of plain text (meta descriptions); tags become spaces so words don't merge. */
    function plain_text(?string $html): string
    {
        return Illuminate\Support\Str::squish(html_entity_decode(strip_tags(preg_replace('/<[^>]*>/', ' $0 ', (string) $html)), ENT_QUOTES | ENT_HTML5));
    }
}

if (! function_exists('assistant_settings')) {
    /**
     * Sapa Madani (virtual assistant) settings from ppid_settings.
     * wa_url is set when a WhatsApp number is configured: extra WA links (home hero, chat panel) next to the AI chat.
     */
    function assistant_settings(): array
    {
        $values = App\Models\PpidSetting::whereIn('key', ['assistant_enabled', 'assistant_whatsapp', 'assistant_api_url', 'assistant_prompt'])
            ->pluck('value', 'key');

        return [
            'enabled' => ($values['assistant_enabled'] ?? '1') === '1',
            'whatsapp' => $values['assistant_whatsapp'] ?? '',
            'api_url' => $values['assistant_api_url'] ?? '',
            'prompt' => $values['assistant_prompt'] ?? '',
            'wa_url' => wa_link($values['assistant_whatsapp'] ?? null),
        ];
    }
}

if (! function_exists('wa_link')) {
    /** "0812-3456 789" / "+62812…" → https://wa.me/62812…; null when no usable number. */
    function wa_link(?string $number): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $number);
        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        }

        return strlen($digits) >= 9 ? 'https://wa.me/' . $digits : null;
    }
}

if (! function_exists('visi_misi')) {
    /**
     * Split admin-edited Visi & Misi HTML into ['visi' => html, 'misi' => [li html…], 'rest' => html]
     * when it follows the "<h3>Visi</h3>…<h3>Misi</h3><ol>…" pattern (h2–h4, any case).
     * Null when it doesn't (or Misi has no list items) → the view shows the content as-is.
     */
    function visi_misi(?string $html): ?array
    {
        $h = fn ($word) => '<h[2-4][^>]*>\s*(?:<[^>]+>\s*)*'.$word.'\s*(?:<[^>]+>\s*)*<\/h[2-4]>';
        if (! preg_match('/^\s*'.$h('Visi').'(.*?)'.$h('Misi').'(.*)$/is', (string) $html, $m)
            || ! preg_match('/^\s*<(ol|ul)[^>]*>(.*?)<\/\1>(.*)$/is', $m[2], $list)
            || ! preg_match_all('/<li[^>]*>(.*?)<\/li>/is', $list[2], $items)) {
            return null;
        }

        return ['visi' => trim($m[1]), 'misi' => array_map('trim', $items[1]), 'rest' => trim($list[3])];
    }
}

if (! function_exists('content_sections')) {
    /**
     * Split admin-edited HTML at its h2–h4 headings:
     * ['intro' => html, 'sections' => [['title' => text, 'body' => html, 'items' => [li html…], 'after' => html]…]].
     * 'items' holds the first <ol>/<ul> of a section (body = text before it, after = text after it).
     * Null when there is no heading → the view shows the content as-is.
     */
    function content_sections(?string $html): ?array
    {
        $parts = preg_split('/<h[2-4][^>]*>(.*?)<\/h[2-4]>/is', (string) $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        if (count($parts) < 3) {
            return null;
        }

        $sections = [];
        for ($i = 1; $i < count($parts); $i += 2) {
            $body = trim($parts[$i + 1]);
            $items = [];
            $after = '';
            if (preg_match('/^(.*?)<(ol|ul)[^>]*>(.*?)<\/\2>(.*)$/is', $body, $m)
                && preg_match_all('/<li[^>]*>(.*?)<\/li>/is', $m[3], $li)) {
                [$body, $items, $after] = [trim($m[1]), array_map('trim', $li[1]), trim($m[4])];
            }
            $sections[] = ['title' => trim(html_entity_decode(strip_tags($parts[$i]))), 'body' => $body, 'items' => $items, 'after' => $after];
        }

        return ['intro' => trim($parts[0]), 'sections' => $sections];
    }
}

if (! function_exists('table_rows')) {
    /**
     * First <table> of admin-edited HTML as plain-text cells:
     * ['intro' => html before, 'head' => [th…], 'rows' => [[td…]…], 'after' => html after].
     * Null when there is no table with body rows → the view shows the content as-is.
     */
    function table_rows(?string $html): ?array
    {
        if (! preg_match('/^(.*?)<table[^>]*>(.*?)<\/table>(.*)$/is', (string) $html, $m)) {
            return null;
        }

        $text = fn ($cell) => trim(html_entity_decode(strip_tags($cell)));
        preg_match_all('/<th[^>]*>(.*?)<\/th>/is', $m[2], $th);
        $rows = [];
        preg_match_all('/<tr[^>]*>(.*?)<\/tr>/is', $m[2], $tr);
        foreach ($tr[1] as $row) {
            if (preg_match_all('/<td[^>]*>(.*?)<\/td>/is', $row, $td)) {
                $rows[] = array_map($text, $td[1]);
            }
        }

        return $rows ? ['intro' => trim($m[1]), 'head' => array_map($text, $th[1]), 'rows' => $rows, 'after' => trim($m[3])] : null;
    }
}
