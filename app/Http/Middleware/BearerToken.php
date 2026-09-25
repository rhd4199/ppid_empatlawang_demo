<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BearerToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = (string) config('services.api.token');

        // Empty API_TOKEN keeps the API closed instead of accepting "Bearer ".
        if ($token === '' || ! hash_equals($token, (string) $request->bearerToken())) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return $next($request);
    }
}
