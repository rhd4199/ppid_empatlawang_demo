<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // PHP discards the whole request past post_max_size; without this the admin
        // just gets a blank 413 page and no idea which upload was too big.
        $exceptions->render(function (\Illuminate\Http\Exceptions\PostTooLargeException $e) {
            return back()->withErrors([
                'upload' => 'Ukuran total file terlalu besar (batas server '.ini_get('post_max_size')
                    .'). Silakan upload lebih sedikit file sekaligus.',
            ]);
        });
    })->create();
