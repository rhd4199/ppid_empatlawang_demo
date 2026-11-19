<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fixed card order; unknown types go last.
        $order = array_flip(['general', 'visi_misi', 'struktur', 'tugas_fungsi']);
        $profiles = \App\Models\Profile::orderBy('id')->get()
            ->sortBy(fn ($profile) => $order[$profile->type] ?? 99)
            ->values();
        return view('profile.index', compact('profiles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($slug)
    {
        $profile = \App\Models\Profile::where('slug', $slug)->firstOrFail();
        return view('profile.show', compact('profile'));
    }
}
