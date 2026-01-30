<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('contact.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'subject' => 'required',
            'message' => 'required',
            'phone' => 'nullable'
        ]);

        \App\Models\Contact::create($validated);

        return redirect()->route('contact.index')->with('success', 'Pesan Anda berhasil dikirim. Kami akan segera menghubungi Anda.');
    }
}
