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
        $procurements = \App\Models\Procurement::latest()->paginate(10);
        return view('procurement.index', compact('procurements'));
    }
}
