<?php

namespace App\Http\Controllers;

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
        return view('report.index', compact('reports'));
    }
}
