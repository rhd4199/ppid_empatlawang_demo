<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CitizenReport;
use Illuminate\Http\Request;

/** Reports recorded by Sapa Madani (catat_laporan); officers follow them up here. */
class CitizenReportController extends Controller
{
    public function index(Request $request)
    {
        $status = isset(CitizenReport::STATUSES[$request->query('status')]) ? $request->query('status') : null;
        $q = is_string($request->query('q')) ? trim(mb_substr($request->query('q'), 0, 100)) : '';

        $reports = CitizenReport::with('conversation')
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($q !== '', function ($query) use ($q) {
                $like = '%' . mb_strtolower($q) . '%';
                $query->where(fn ($w) => $w->whereRaw('LOWER(code) LIKE ?', [$like])->orWhereRaw('LOWER(description) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(location) LIKE ?', [$like])->orWhereRaw('LOWER(kecamatan) LIKE ?', [$like]));
            })
            ->latest('id')->paginate(20)->withQueryString();

        return view('admin.citizen_report.index', [
            'reports' => $reports,
            'counts' => CitizenReport::selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status'),
            'status' => $status,
            'q' => $q,
        ]);
    }

    public function update(Request $request, CitizenReport $report)
    {
        $report->update($request->validate([
            'status' => 'required|in:' . implode(',', array_keys(CitizenReport::STATUSES)),
            'officer_notes' => 'nullable|string|max:2000',
        ]));

        return back()->with('success', "Laporan {$report->code} diperbarui.");
    }
}
