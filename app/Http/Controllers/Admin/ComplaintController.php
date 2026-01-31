<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint; // Assuming this model exists
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    public function index()
    {
        $complaints = Complaint::latest()->paginate(10);
        return view('admin.complaint.index', compact('complaints'));
    }

    public function show($id)
    {
        $complaint = Complaint::findOrFail($id);
        return view('admin.complaint.show', compact('complaint'));
    }

    public function update(Request $request, $id)
    {
        $complaint = Complaint::findOrFail($id);
        
        $validated = $request->validate([
            'status' => 'required|in:pending,processed,resolved,rejected',
            'admin_reply' => 'nullable|string'
        ]);

        $complaint->update($validated);

        return redirect()->route('admin.complaints.show', $id)
            ->with('success', 'Status keberatan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $complaint = Complaint::findOrFail($id);
        $complaint->delete();

        return redirect()->route('admin.complaints.index')
            ->with('success', 'Pengajuan keberatan berhasil dihapus.');
    }
}
