<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProcurementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $procurements = \App\Models\Procurement::where('category', '!=', 'regulasi_pengadaan')->latest()->get();
        $regulations = \App\Models\Procurement::where('category', 'regulasi_pengadaan')->latest()->get();
        return view('procurement.index', compact('procurements', 'regulations'));
    }
}
