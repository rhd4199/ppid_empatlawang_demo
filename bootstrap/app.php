<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // API clients get JSON errors (401/404/422) even without an Accept header.
        $exceptions->shouldRenderJsonWhen(fn ($request) => $request->is('api/*') || $request->expectsJson());

        // PHP discards the whole request past post_max_size; without this the admin
        // just gets a blank 413 page and no idea which upload was too big.
        $exceptions->render(function (\Illuminate\Http\Exceptions\PostTooLargeException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Payload melebihi post_max_size ('.ini_get('post_max_size').').'], 413);
            }

            return back()->withErrors([
                'upload' => 'Ukuran total file terlalu besar (batas server '.ini_get('post_max_size')
                    .'). Silakan upload lebih sedikit file sekaligus.',
            ]);
        });
    })->create();
