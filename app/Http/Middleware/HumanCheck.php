<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Bot protection for public forms (kontak, permohonan, keberatan), against spam like "ncMUFCMU / 1 / 555":
 * a honeypot field (`website`, hidden from people) and Cloudflare Turnstile when TURNSTILE_SECRET_KEY is set
 * (https://developers.cloudflare.com/turnstile/get-started/server-side-validation/). Routes also get a throttle.
 * Forms include the partial partials/human-check.
 */
class HumanCheck
{
    const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public function handle(Request $request, Closure $next)
    {
        // Bots fill every field; people never see this one.
        if (filled($request->input('website'))) {
            Log::info('Honeypot blocked a form post from ' . $request->ip() . ' to ' . $request->path());
            throw ValidationException::withMessages(['human' => 'Pengiriman ditolak. Silakan coba lagi.']);
        }
        if (filled($secret = config('services.turnstile.secret_key')) && ! self::verify((string) $request->input('cf-turnstile-response'), $secret, $request->ip())) {
            throw ValidationException::withMessages(['human' => 'Verifikasi keamanan gagal atau kedaluwarsa. Centang kembali "Saya bukan robot", lalu kirim ulang.']);
        }

        return $next($request);
    }

    private static function verify(string $token, string $secret, ?string $ip): bool
    {
        if ($token === '' || mb_strlen($token) > 2048) {
            return false;
        }
        try {
            return (bool) Http::asForm()->timeout(8)->post(self::VERIFY_URL, ['secret' => $secret, 'response' => $token, 'remoteip' => $ip])->json('success');
        } catch (\Throwable $e) {
            // Cloudflare unreachable: let the person through (honeypot + throttle still apply) rather than block every citizen.
            Log::warning('Turnstile verify unreachable: ' . class_basename($e));

            return true;
        }
    }
}
