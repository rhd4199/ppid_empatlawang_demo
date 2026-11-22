<?php

namespace App\Http\Controllers;

use App\Models\SppgUnit;
use Illuminate\Http\Request;

class SppgController extends Controller
{
    /** /mbg — MBG kitchens (SPPG) in the regency: summary, kecamatan filter, search. Published rows only. */
    public function index(Request $request)
    {
        $kecamatans = SppgUnit::kecamatanCounts();
        $kecamatan = $kecamatans->has((string) $request->query('kecamatan')) ? (string) $request->query('kecamatan') : null;
        $q = mb_substr(trim((string) $request->query('q')), 0, 100);

        $all = SppgUnit::published()->get(['id', 'status', 'institutions_count', 'checked_at', 'source_url']);
        $units = SppgUnit::published()->search($q)
            ->when($kecamatan, fn ($query) => $query->where('kecamatan', $kecamatan))
            ->orderBy('kecamatan')->orderBy('name')->get();

        return view('sppg.index', [
            'units' => $units,
            'kecamatans' => $kecamatans,
            'kecamatan' => $kecamatan,
            'q' => $q,
            'total' => $all->count(),
            'statusCounts' => $all->countBy('status'),
            'servedTotal' => (int) $all->sum('institutions_count'),
            'checkedAt' => $all->max('checked_at'),
            'sources' => $all->pluck('source_url')->filter()->unique()->values(),
            'mapped' => $units->filter(fn ($u) => $u->latitude && $u->longitude)->values(),
        ]);
    }
}
