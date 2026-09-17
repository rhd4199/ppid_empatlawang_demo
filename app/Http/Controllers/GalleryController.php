<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $galleries = Gallery::where('is_published', true)->latest()->paginate(12);
        return view('gallery.index', compact('galleries'));
    }

    public function show($slug)
    {
        $gallery = Gallery::where('is_published', true)->with(['items' => function($q) {
            $q->orderBy('order', 'asc');
        }])->where('slug', $slug)->first();

        // old links used the numeric id
        if (!$gallery && ctype_digit((string) $slug)) {
            $old = Gallery::where('is_published', true)->findOrFail($slug);
            return redirect()->route('galleries.show', $old->slug, 301);
        }
        abort_unless($gallery, 404);

        return view('gallery.show', compact('gallery'));
    }
}
