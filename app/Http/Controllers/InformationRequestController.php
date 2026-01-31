<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InformationRequestController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('request.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'nik' => 'required',
            'address' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
            'info_requested' => 'required',
            'reason' => 'required',
            'delivery_method' => 'required',
            'ktp_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($request->hasFile('ktp_file')) {
            $path = $request->file('ktp_file')->store('ktp_files', 'public');
            $validated['ktp_file'] = $path;
        }

        $validated['ticket_number'] = 'REG-' . time();
        $validated['status'] = 'pending';

        \App\Models\InformationRequest::create($validated);

        return redirect()->to(route('standard-service.index') . '#permohonan')
            ->with('success_modal_title', 'Permohonan Berhasil Dikirim!')
            ->with('success_modal_message', 'Selamat, permohonan Anda berhasil dibuat dan akan diproses oleh Admin PPID. Silakan menunggu informasi lanjutan, admin kami akan menghubungi Anda melalui kontak yang Anda masukkan.<br><br><strong>Nomor Tiket Anda: ' . $validated['ticket_number'] . '</strong>');
    }

    public function checkStatus()
    {
        return view('request.status');
    }
}
