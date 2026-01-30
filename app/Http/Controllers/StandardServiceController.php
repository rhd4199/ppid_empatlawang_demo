<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StandardServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $documents = \App\Models\Document::where('category', 'standar_layanan')->get();
        return view('standard_service.index', compact('documents'));
    }
}
