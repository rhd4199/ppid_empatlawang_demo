<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Downloads an image from a public http(s) URL into the "public" disk (S3), for API clients that send a URL instead of a file.
 * SSRF guard: only http/https, every resolved IP (and every redirect target) must be public — no localhost, private,
 * link-local (cloud metadata 169.254.x) or reserved ranges.
 * ponytail: the host is resolved by us and again by curl (tiny DNS-rebinding window); pin the IP with CURLOPT_RESOLVE if that matters.
 */
class RemoteImage
{
    public const MAX_BYTES = 5 * 1024 * 1024;

    private const TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    /** @return string stored path, e.g. news/abc.jpg */
    public static function store(string $url, string $dir, string $field = 'image_url'): string
    {
        $fail = fn (string $message) => throw ValidationException::withMessages([$field => $message]);

        self::assertPublic($url) ?: $fail('URL gambar harus http(s) ke alamat publik.');

        try {
            $response = Http::timeout(15)->connectTimeout(5)
                ->withOptions([
                    'allow_redirects' => [
                        'max' => 3,
                        'protocols' => ['http', 'https'],
                        'on_redirect' => function ($request, $response, $uri) {
                            if (! self::assertPublic((string) $uri)) {
                                throw new \RuntimeException('redirect to a non-public address');
                            }
                        },
                    ],
                    // stop early when the server announces a too-large body
                    'on_headers' => function ($response) {
                        if ((int) $response->getHeaderLine('Content-Length') > self::MAX_BYTES) {
                            throw new \RuntimeException('image too large');
                        }
                    },
                ])
                ->get($url);
        } catch (\Throwable $e) {
            $fail('Gambar tidak dapat diunduh dari URL tersebut.');
        }

        if (! $response->successful()) {
            $fail('Gambar tidak dapat diunduh (HTTP ' . $response->status() . ').');
        }
        $body = $response->body();
        if (strlen($body) > self::MAX_BYTES) {
            $fail('Ukuran gambar maksimal 5 MB.');
        }
        // type from the bytes, not from the URL or Content-Type header
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($body);
        if (! isset(self::TYPES[$mime]) || @getimagesizefromstring($body) === false) {
            $fail('URL harus berisi gambar JPG, PNG, atau WEBP.');
        }

        $path = trim($dir, '/') . '/' . Str::random(40) . '.' . self::TYPES[$mime];
        Storage::disk('public')->put($path, $body);

        return $path;
    }

    /** http(s) URL whose host resolves only to public IPs. */
    public static function assertPublic(string $url): bool
    {
        $parts = parse_url($url);
        $host = trim($parts['host'] ?? '', '[]');
        if (! in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true) || $host === '' || isset($parts['user'])) {
            return false;
        }
        $ips = filter_var($host, FILTER_VALIDATE_IP) ? [$host] : array_merge(
            gethostbynamel($host) ?: [],
            array_column(@dns_get_record($host, DNS_AAAA) ?: [], 'ipv6'),
        );

        return $ips !== [] && collect($ips)->every(
            // "::ffff:127.0.0.1" is IPv4 in IPv6 clothing
            fn ($ip) => filter_var(preg_replace('/^::ffff:/i', '', $ip), FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false
        );
    }
}
