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
