<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API_TOKEN = full admin access. API_INJECT_TOKEN = content pushers (scraper, n8n…): only list/show/create/update
 * news and galleries (+ add gallery photos) — no delete, bulk, toggles, complaints, requests or settings.
 * No token at all = read-only public content (PUBLIC_READ): published items only; messages, information
 * requests and complaints (personal data) stay behind a token.
 */
class BearerToken
{
    private const INJECT_ROUTES = [
        'api.news.index', 'api.news.show', 'api.news.store', 'api.news.update',
        'api.galleries.index', 'api.galleries.show', 'api.galleries.store', 'api.galleries.update', 'api.galleries.photos',
    ];

    private const PUBLIC_READ = [
        'api/v1/documents', 'api/v1/documents/{document}', 'api/v1/news', 'api/v1/news/{news}',
        'api/v1/galleries', 'api/v1/galleries/{gallery}', 'api/v1/events', 'api/v1/events/{event}',
        'api/v1/officials', 'api/v1/officials/{official}', 'api/v1/profiles', 'api/v1/profiles/{slug}',
        'api/v1/settings/contact', 'api/v1/settings/stats',
    ];

    public function handle(Request $request, Closure $next)
    {
        if (! $request->headers->has('Authorization') && $request->isMethod('GET')
            && in_array($request->route()?->uri(), self::PUBLIC_READ, true)) {
            return $this->publicRead($request, $next);
        }

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

    /** Lists are filtered to published items; a single unpublished item answers 404. */
    private function publicRead(Request $request, Closure $next)
    {
        $request->query->set('is_published', '1');
        $request->merge(['is_published' => '1']);

        $response = $next($request);
        if ($response instanceof JsonResponse) {
            $data = $response->getData(true);
            if (is_array($data) && array_key_exists('is_published', $data) && ! $data['is_published']) {
                return response()->json(['message' => 'Not found.'], 404);
            }
        }

        return $response;
    }
}
