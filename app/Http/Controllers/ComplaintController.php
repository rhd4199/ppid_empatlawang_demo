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

        return redirect()->to(route('standard-service.index') . '#keberatan')
            ->with('success_modal_title', 'Pengajuan Keberatan Berhasil!')
            ->with('success_modal_message', 'Selamat, pengajuan keberatan Anda berhasil dibuat dan akan diproses oleh Admin PPID. Silakan menunggu informasi lanjutan, admin kami akan menghubungi Anda melalui kontak yang Anda masukkan.<br><br><strong>Nomor Tiket Aduan Anda: ' . $validated['ticket_number'] . '</strong>');
    }
}
