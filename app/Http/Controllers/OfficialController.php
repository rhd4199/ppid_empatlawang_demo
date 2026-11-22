<?php

namespace App\Http\Controllers;

use App\Models\Official;

class OfficialController extends Controller
{
    public function index()
    {
        $all = Official::where('is_published', true)
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        // ponytail: leaders = Pemkab order 1–3 (Bupati, Wakil, Sekda); add a `level` column if that grows.
        [$leaders, $rest] = $all->partition(fn ($o) => $o->group === 'pemkab' && $o->order >= 1 && $o->order <= 3);
        $groups = $rest->groupBy('group');
        $officials = $groups->pull('pemkab', collect());
        // Other sections (Forkopimda, DPRD, Camat) in Official::GROUPS order.
        $sections = collect(Official::GROUPS)->except('pemkab')
            ->map(fn ($label, $key) => ['label' => $label, 'officials' => $groups->get($key, collect())])
            ->filter(fn ($s) => $s['officials']->isNotEmpty());

        return view('official.index', compact('leaders', 'officials', 'sections'));
    }
}
