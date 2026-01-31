<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;

class ProcurementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $documents = Document::whereIn('category', [
            'pengadaan_info',
            'pengadaan_regulasi'
        ])
        ->where('is_published', true)
        ->latest()
        ->get();
        
        return view('procurement.index', compact('documents'));
    }
}
