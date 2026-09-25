<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InformationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InformationRequestController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'status' => 'nullable|in:pending,processed,approved,rejected',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        return InformationRequest::query()
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->ticket_number, fn ($q, $t) => $q->where('ticket_number', $t))
            ->latest()
            ->paginate($request->integer('per_page', 15));
    }

    public function show(InformationRequest $informationRequest)
    {
        return $informationRequest;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'nik' => 'required|string|max:32',
            'address' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
            'info_requested' => 'required|string',
            'reason' => 'required|string',
            'delivery_method' => 'required|string|max:50',
            'ktp_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($request->hasFile('ktp_file')) {
            $data['ktp_file'] = $request->file('ktp_file')->store('ktp_files', 'public');
        }
        $data['ticket_number'] = 'REG-'.time();
        $data['status'] = 'pending';

        return response()->json(InformationRequest::create($data), 201);
    }

    public function update(Request $request, InformationRequest $informationRequest)
    {
        $informationRequest->update($request->validate([
            'status' => 'required|in:pending,processed,approved,rejected',
            'admin_note' => 'nullable|string',
        ]));

        return $informationRequest;
    }

    public function destroy(InformationRequest $informationRequest)
    {
        if ($informationRequest->ktp_file) {
            Storage::disk('public')->delete($informationRequest->ktp_file);
        }
        $informationRequest->delete();

        return response()->noContent();
    }
}
