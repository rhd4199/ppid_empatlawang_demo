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
        $requests = InformationRequest::all();

        $rejected = $requests->where('status', 'rejected');

        $resolved = $requests->whereIn('status', ['approved', 'rejected']);
        $avgDays = $resolved->isNotEmpty()
            ? round($resolved->avg(fn ($r) => $r->created_at->diffInDays($r->updated_at)), 1)
            : null;

        return [
            'total' => $requests->count(),
            // ponytail: schema has no "partial vs full" distinction on approved requests, only one status
            'approved' => $requests->where('status', 'approved')->count(),
            'rejected' => $rejected->count(),
            'avg_days' => $avgDays,
            'rejection_reasons' => $rejected->pluck('admin_note')->filter()->values(),
        ];
    }
}
