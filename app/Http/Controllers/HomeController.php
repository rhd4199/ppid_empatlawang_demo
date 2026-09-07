<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\News;
use App\Models\Gallery;
use App\Models\Document;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $news = News::where('is_published', true)
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->take(3)
            ->get();
        $galleries = Gallery::latest()->take(9)->get(); // Fetch latest 9 for carousel (3 slides x 3 items)

        // Informasi Serta Merta (peringatan dini/evakuasi) highlighted as home sections
        $emergencyInfo = Document::where('category', 'informasi-publik-serta-merta')
            ->where('is_published', true)
            ->orderBy('id')
            ->get();

        return view('home', compact('news', 'galleries', 'emergencyInfo'));
    }
}
