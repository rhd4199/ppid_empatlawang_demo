<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use App\Models\ContactSetting;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Sapa Madani web chat calls OpenAI: per IP 20 a minute and 150 a day, so a bot cannot burn the AI budget.
        \Illuminate\Support\Facades\RateLimiter::for('assistant', fn (\Illuminate\Http\Request $r) => [
            \Illuminate\Cache\RateLimiting\Limit::perMinute(20)->by('m' . $r->ip()),
            \Illuminate\Cache\RateLimiting\Limit::perDay(150)->by('d' . $r->ip()),
        ]);

        // share contact settings (social media, phone, email) with the public layout
        View::composer('layouts.app', function ($view) {
            $view->with('contactSettings', ContactSetting::first());
            $view->with('assistant', assistant_settings());
        });
    }
}
