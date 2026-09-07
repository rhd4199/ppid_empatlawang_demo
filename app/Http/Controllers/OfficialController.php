<?php

namespace App\Http\Controllers;

use App\Models\Official;

class OfficialController extends Controller
{
    public function index()
    {
        $officials = Official::where('is_published', true)
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        return view('official.index', compact('officials'));
    }
}
