<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // ?tipe=foto|video; anything else = all albums
        $type = ['foto' => 'photo', 'video' => 'video'][(string) $request->query('tipe')] ?? null;
        $galleries = Gallery::where('is_published', true)
            ->when($type, fn ($q) => $q->where('type', $type))
            ->withCount('items')->latest()->paginate(12)->withQueryString();

        return view('gallery.index', compact('galleries', 'type'));
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
