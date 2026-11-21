<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * API_TOKEN = full admin access. API_INJECT_TOKEN = content pushers (scraper, n8n…): only list/show/create/update
 * news and galleries (+ add gallery photos) — no delete, bulk, toggles, complaints, requests or settings.
 */
class BearerToken
{
    private const INJECT_ROUTES = [
        'api.news.index', 'api.news.show', 'api.news.store', 'api.news.update',
        'api.galleries.index', 'api.galleries.show', 'api.galleries.store', 'api.galleries.update', 'api.galleries.photos',
    ];

    public function handle(Request $request, Closure $next)
    {
        $given = (string) $request->bearerToken();
        $matches = fn (?string $token) => (string) $token !== '' && hash_equals((string) $token, $given);

        // Empty tokens keep the API closed instead of accepting "Bearer ".
        if ($matches(config('services.api.token'))) {
            return $next($request);
        }
        if ($matches(config('services.api.inject_token'))) {
            return in_array($request->route()?->getName(), self::INJECT_ROUTES, true)
                ? $next($request)
                : response()->json(['message' => 'This token may only create/update news and galleries.'], 403);
        }

        return response()->json(['message' => 'Unauthenticated.'], 401);
    }
}
