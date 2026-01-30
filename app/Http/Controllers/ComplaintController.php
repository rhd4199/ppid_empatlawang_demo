<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('complaint.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'reason_complaint' => 'required',
            'request_ticket_number' => 'nullable'
        ]);

        $validated['ticket_number'] = 'ADU-' . time();
        $validated['status'] = 'pending';

        \App\Models\Complaint::create($validated);

        return redirect()->route('home')->with('success', 'Pengajuan keberatan berhasil dikirim! Tiket Aduan Anda: ' . $validated['ticket_number']);
    }
}
