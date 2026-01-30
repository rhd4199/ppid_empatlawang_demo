<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GalleryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $galleries = \App\Models\Gallery::latest()->paginate(12);
        return view('gallery.index', compact('galleries'));
    }
}
