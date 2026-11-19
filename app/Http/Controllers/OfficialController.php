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

        // ponytail: leaders = order 1–3 (Bupati, Wakil, Sekda); add a `level` column if that grows.
        [$leaders, $officials] = $officials->partition(fn ($o) => $o->order >= 1 && $o->order <= 3);

        return view('official.index', compact('leaders', 'officials'));
    }
}
