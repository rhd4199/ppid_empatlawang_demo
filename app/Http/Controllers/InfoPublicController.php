<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;

class InfoPublicController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Document::query();
        
        // Filter only public information categories
        $query->whereIn('category', [
            'informasi-publik-berkala',
            'informasi-publik-serta-merta',
            'informasi-publik-setiap-saat',
            'informasi-publik-dikecualikan'
        ])->where('is_published', true);

        // Handle category filter
        if ($request->has('category') && $request->category != '') {
            $categoryMap = [
                'berkala' => 'informasi-publik-berkala',
                'serta-merta' => 'informasi-publik-serta-merta',
                'setiap-saat' => 'informasi-publik-setiap-saat',
                'dikecualikan' => 'informasi-publik-dikecualikan',
            ];

            if (array_key_exists($request->category, $categoryMap)) {
                $query->where('category', $categoryMap[$request->category]);
            }
        }

        if ($request->has('search') && $request->search != '') {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $infos = $query->latest()->paginate(10);

        if ($request->ajax()) {
            return view('info_public._list', compact('infos'))->render();
        }

        return view('info_public.index', compact('infos'));
    }
}
