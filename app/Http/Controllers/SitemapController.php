<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\Document;
use App\Models\Gallery;
use App\Models\News;
use App\Models\Profile;
use App\Models\ServiceRequirement;

class SitemapController extends Controller
{
    public function sitemap()
    {
        $urls = collect([
            'home', 'profiles.index', 'officials.index', 'informasi-publik.index', 'standard-service.index', 'service-requirements.index',
            'reports.index', 'news.index', 'galleries.index', 'events.index', 'procurements.index', 'sppg.index', 'agencies.index',
            'contact.index', 'request.create', 'complaint.create',
        ])->map(fn ($name) => ['loc' => route($name), 'lastmod' => null]);

        $add = fn ($items, $route, $key = 'slug') => $items->each(fn ($item) => $urls->push([
            'loc' => route($route, $item->{$key}),
            'lastmod' => $item->updated_at,
        ]));

        $add(Profile::select('slug', 'updated_at')->get(), 'profiles.show');
        $add(News::where('is_published', true)->where('published_at', '<=', now())->select('slug', 'updated_at')->get(), 'news.show');
        $add(Gallery::where('is_published', true)->whereNotNull('slug')->select('slug', 'updated_at')->get(), 'galleries.show');
        $add(ServiceRequirement::where('is_published', true)->select('slug', 'updated_at')->get(), 'service-requirements.show');
        $add(Agency::where('is_published', true)->select('slug', 'updated_at')->get(), 'agencies.show');
        $add(Document::where('is_published', true)->select('id', 'updated_at')->get(), 'informasi-publik.show', 'id');

        return response()->view('sitemap', ['urls' => $urls], 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots()
    {
        // login/password pages stay crawlable so bots can see their noindex tag
        $body = "User-agent: *\nDisallow: /admin\n\nSitemap: ".route('sitemap')."\n";

        return response($body, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
