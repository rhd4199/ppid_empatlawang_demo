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

    public function show($id)
    {
        $gallery = Gallery::where('is_published', true)->with(['items' => function($q) {
            $q->orderBy('order', 'asc');
        }])->findOrFail($id);
        return view('gallery.show', compact('gallery'));
    }
}
