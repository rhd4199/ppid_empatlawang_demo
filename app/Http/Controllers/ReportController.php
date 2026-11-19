<?php

namespace App\Http\Controllers;

use App\Models\InformationRequest;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reports = \App\Models\Document::whereIn('category', ['laporan_pemda', 'laporan_ppid'])
            ->where('is_published', true)
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = $this->requestStats();

        return view('report.index', compact('reports', 'stats'));
    }

    /**
     * Ringkasan Laporan Layanan Informasi Publik (checklist Monev item 8):
     * jumlah permohonan diterima, dikabulkan, waktu rata-rata, dan alasan penolakan.
     */
    private function requestStats(): array
    {
        // Public page: never load whole rows (they hold NIK, KTP, addresses), only what is shown.
        $counts = InformationRequest::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        // ponytail: day diff stays in PHP (DATEDIFF differs per driver); only two columns are read
        $resolved = InformationRequest::whereIn('status', ['approved', 'rejected'])->get(['created_at', 'updated_at']);
        $avgDays = $resolved->isNotEmpty()
            ? round($resolved->avg(fn ($r) => $r->created_at->diffInDays($r->updated_at)), 1)
            : null;

        return [
            'total' => (int) $counts->sum(),
            // ponytail: schema has no "partial vs full" distinction on approved requests, only one status
            'approved' => (int) ($counts['approved'] ?? 0),
            'rejected' => (int) ($counts['rejected'] ?? 0),
            'avg_days' => $avgDays,
            'rejection_reasons' => InformationRequest::where('status', 'rejected')->pluck('admin_note')->filter()->values(),
        ];
    }
}
