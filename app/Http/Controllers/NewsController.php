<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\News;

class NewsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch the explicit headline
        $headline = \App\Models\News::where('is_published', true)
            ->where('is_headline', true)
            ->where('published_at', '<=', now())
            ->first();

        // Query for other news
        $query = \App\Models\News::where('is_published', true)
            ->where('published_at', '<=', now());
        
        // If headline exists, exclude it from the main list
        if ($headline) {
            $query->where('id', '!=', $headline->id);
        }

        $news = $query->orderBy('published_at', 'desc')
            ->paginate(9);

        return view('news.index', compact('news', 'headline'));
    }

    /**
     * Display the specified resource.
     */
    public function show(News $news) // bound by slug (see routes/web.php)
    {
        // drafts and scheduled posts must not be reachable (or indexed) by slug
        abort_unless($news->is_published && $news->published_at && now()->gte($news->published_at), 404);

        return view('news.show', compact('news'));
    }
}
