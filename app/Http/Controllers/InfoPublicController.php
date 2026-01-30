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
        
        if ($request->has('category') && $request->category != '') {
            $query->where('category', $request->category);
        }

        if ($request->has('search') && $request->search != '') {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        $infos = $query->paginate(10);

        if ($request->ajax()) {
            return view('info_public._list', compact('infos'))->render();
        }

        return view('info_public.index', compact('infos'));
    }
}
