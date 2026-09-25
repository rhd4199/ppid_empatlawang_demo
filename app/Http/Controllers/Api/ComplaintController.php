<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'status' => 'nullable|in:pending,processed,resolved,rejected',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        return Complaint::query()
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->ticket_number, fn ($q, $t) => $q->where('ticket_number', $t))
            ->latest()
            ->paginate($request->integer('per_page', 15));
    }

    public function show(Complaint $complaint)
    {
        return $complaint;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
            'reason_complaint' => 'required|string',
            'request_ticket_number' => 'nullable|string|max:50',
        ]);

        $data['ticket_number'] = 'ADU-'.time();
        $data['status'] = 'pending';

        return response()->json(Complaint::create($data), 201);
    }

    public function update(Request $request, Complaint $complaint)
    {
        $complaint->update($request->validate([
            'status' => 'required|in:pending,processed,resolved,rejected',
            'admin_reply' => 'nullable|string',
        ]));

        return $complaint;
    }

    public function destroy(Complaint $complaint)
    {
        $complaint->delete();

        return response()->noContent();
    }
}
