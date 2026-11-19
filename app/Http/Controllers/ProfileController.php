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
        $profiles = $this->orderedProfiles(\App\Models\Profile::query());
        return view('profile.index', compact('profiles'));
    }

    // Fixed order (card list, side menu); unknown types go last.
    private function orderedProfiles($query)
    {
        $order = array_flip(['general', 'visi_misi', 'struktur', 'tugas_fungsi']);

        return $query->orderBy('id')->get()
            ->sortBy(fn ($profile) => $order[$profile->type] ?? 99)
            ->values();
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

        // Side menu: every profile subpage, then Pejabat.
        $menu = $this->orderedProfiles(\App\Models\Profile::select('id', 'title', 'slug', 'type'))
            ->map(fn ($item) => [
                'title' => $item->title,
                'url' => route('profiles.show', $item->slug),
                'active' => $item->id === $profile->id,
            ])
            ->push(['title' => 'Profil Pejabat', 'url' => route('officials.index'), 'active' => false]);

        $index = $menu->search(fn ($item) => $item['active']);
        $prev = $index > 0 ? $menu[$index - 1] : null;
        $next = $menu[$index + 1] ?? null;

        return view('profile.show', compact('profile', 'menu', 'prev', 'next'));
    }
}
