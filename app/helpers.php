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

        return Storage::disk('public')->url($path);
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
     * wa_url is set when a WhatsApp number is configured: the button then opens WA instead of the chat panel.
     */
    function assistant_settings(): array
    {
        $values = App\Models\PpidSetting::whereIn('key', ['assistant_enabled', 'assistant_whatsapp', 'assistant_api_url'])
            ->pluck('value', 'key');

        return [
            'enabled' => ($values['assistant_enabled'] ?? '1') === '1',
            'whatsapp' => $values['assistant_whatsapp'] ?? '',
            'api_url' => $values['assistant_api_url'] ?? '',
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
