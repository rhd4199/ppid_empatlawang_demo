<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;

class StandardServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $documents = Document::whereIn('category', [
            'standar_layanan_alur',
            'standar_layanan_tata_cara',
            'standar_layanan_permohonan',
            'standar_layanan_keberatan',
            'standar_layanan_sengketa',
            'standar_layanan_sop',
            'standar_layanan_maklumat',
            'standar_layanan_biaya'
        ])
        ->where('is_published', true)
        ->latest()
        ->get();
        
        return view('standard_service.index', compact('documents'));
    }
}
