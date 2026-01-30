<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InfoPublicController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = \App\Models\InfoPublic::query();
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }
        $infos = $query->paginate(10);
        return view('info_public.index', compact('infos'));
    }
}
