<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InformationRequest; // Assuming this model exists
use Illuminate\Http\Request;

class InformationRequestController extends Controller
{
    public function index()
    {
        $requests = InformationRequest::latest()->paginate(10);
        return view('admin.request.index', compact('requests'));
    }

    public function show($id)
    {
        $request = InformationRequest::findOrFail($id);
        return view('admin.request.show', compact('request'));
    }

    public function update(Request $request, $id)
    {
        $infoRequest = InformationRequest::findOrFail($id);
        
        $validated = $request->validate([
            'status' => 'required|in:pending,processed,approved,rejected',
            'admin_note' => 'nullable|string'
        ]);

        $infoRequest->update($validated);

        return redirect()->route('admin.requests.show', $id)
            ->with('success', 'Status permohonan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $request = InformationRequest::findOrFail($id);
        
        if ($request->ktp_file) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($request->ktp_file);
        }
        
        $request->delete();

        return redirect()->route('admin.requests.index')
            ->with('success', 'Permohonan berhasil dihapus.');
    }
}
