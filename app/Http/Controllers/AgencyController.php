<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use Illuminate\Http\Request;

class AgencyController extends Controller
{
    /** /instansi — perangkat daerah per category, with search. Published rows only. */
    public function index(Request $request)
    {
        $q = mb_substr(trim((string) $request->query('q')), 0, 100);
        $category = isset(Agency::CATEGORIES[(string) $request->query('kategori')]) ? (string) $request->query('kategori') : null;

        $agencies = Agency::published()->with('official')->search($q)
            ->when($category, fn ($query) => $query->where('category', $category))
            ->ordered()->get();

        return view('agency.index', [
            'groups' => collect(Agency::CATEGORIES)->map(fn ($label, $key) => $agencies->where('category', $key))->filter(fn ($rows) => $rows->isNotEmpty()),
            'counts' => Agency::published()->selectRaw('category, COUNT(*) AS total')->groupBy('category')->pluck('total', 'category'),
            'total' => $agencies->count(),
            'q' => $q,
            'category' => $category,
        ]);
    }

    public function show(string $slug)
    {
        $agency = Agency::published()->with('official')->where('slug', $slug)->firstOrFail();

        return view('agency.show', [
            'agency' => $agency,
            'related' => Agency::published()->where('category', $agency->category)->whereKeyNot($agency->id)->ordered()->get(['name', 'slug']),
        ]);
    }
}
