<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NewsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $news = \App\Models\News::where('is_published', true)->orderBy('published_at', 'desc')->paginate(9);
        return view('news.index', compact('news'));
    }

    /**
     * Display the specified resource.
     */
    public function show(News $news) // Route binding using ID for now, or change to slug if model binding updated
    {
         // Assuming route uses resource which expects ID by default unless customized.
         // But in migration we added slug. Let's use standard find or slug if we customize route.
         // For simplicity with resource route, let's use implicit binding or find.
         // If we want slug: Route::resource...->parameters(['berita' => 'news:slug'])
         // Let's assume standard ID for now or update show method to find by slug if passed.
         
         return view('news.show', compact('news'));
    }
}
