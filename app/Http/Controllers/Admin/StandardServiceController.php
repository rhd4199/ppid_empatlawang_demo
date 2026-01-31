<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StandardServiceController extends Controller
{
    public function index(Request $request)
    {
        $sortField = $request->query('sort_field', 'created_at');
        $sortDirection = $request->query('sort_direction', 'desc');
        
        $allowedSorts = ['title', 'category', 'created_at', 'is_published'];
        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'created_at';
        }
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        $documents = Document::whereIn('category', [
            'standar_layanan_alur',
            'standar_layanan_tata_cara',
            'standar_layanan_permohonan',
            'standar_layanan_keberatan',
            'standar_layanan_sengketa',
            'standar_layanan_sop',
            'standar_layanan_maklumat',
            'standar_layanan_biaya'
        ])->orderBy($sortField, $sortDirection)->get();
        
        return view('admin.standard_service.index', compact('documents'));
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:documents,id',
            'action' => 'required|in:delete,publish,unpublish'
        ]);

        $ids = $request->ids;
        $action = $request->action;
        $count = count($ids);

        if ($action === 'delete') {
            $documents = Document::whereIn('id', $ids)->get();
            foreach ($documents as $document) {
                if ($document->file_path && Storage::exists('public/' . $document->file_path)) {
                    Storage::delete('public/' . $document->file_path);
                }
                $document->delete();
            }
            return redirect()->route('admin.standard-service.index')->with('success', "$count dokumen berhasil dihapus.");
        } elseif ($action === 'publish') {
            Document::whereIn('id', $ids)->update(['is_published' => true]);
            return redirect()->route('admin.standard-service.index')->with('success', "$count dokumen berhasil dipublikasikan.");
        } elseif ($action === 'unpublish') {
            Document::whereIn('id', $ids)->update(['is_published' => false]);
            return redirect()->route('admin.standard-service.index')->with('success', "$count dokumen berhasil ditarik dari publikasi.");
        }

        return redirect()->route('admin.standard-service.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|in:standar_layanan_alur,standar_layanan_tata_cara,standar_layanan_permohonan,standar_layanan_keberatan,standar_layanan_sengketa,standar_layanan_sop,standar_layanan_maklumat,standar_layanan_biaya',
            'file_path' => 'nullable|file|mimes:pdf,doc,docx,xls,csv,xlsx,jpg,png|max:10240', // 10MB max
            'description' => 'nullable|string',
            'is_published' => 'nullable|boolean',
        ]);

        $path = null;
        if ($request->hasFile('file_path')) {
            $path = $request->file('file_path')->store('documents', 'public');
        }

        Document::create([
            'title' => $request->title,
            'category' => $request->category,
            'file_path' => $path,
            'description' => $request->description,
            'is_published' => $request->has('is_published') ? $request->is_published : true,
        ]);

        return redirect()->route('admin.standard-service.index')->with('success', 'Dokumen standar layanan berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $document = Document::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|in:standar_layanan_alur,standar_layanan_tata_cara,standar_layanan_permohonan,standar_layanan_keberatan,standar_layanan_sengketa,standar_layanan_sop,standar_layanan_maklumat,standar_layanan_biaya',
            'file_path' => 'nullable|file|mimes:pdf,doc,docx,xls,csv,xlsx,jpg,png|max:10240',
            'description' => 'nullable|string',
            'is_published' => 'nullable|boolean',
        ]);

        $data = [
            'title' => $request->title,
            'category' => $request->category,
            'description' => $request->description,
            'is_published' => $request->has('is_published') ? $request->is_published : $document->is_published,
        ];

        if ($request->hasFile('file_path')) {
            // Delete old file
            if ($document->file_path && Storage::exists('public/' . $document->file_path)) {
                Storage::delete('public/' . $document->file_path);
            }
            
            $path = $request->file('file_path')->store('documents', 'public');
            $data['file_path'] = $path;
        }

        $document->update($data);

        return redirect()->route('admin.standard-service.index')->with('success', 'Dokumen standar layanan berhasil diperbarui.');
    }

    public function toggleStatus($id)
    {
        $document = Document::findOrFail($id);
        $document->is_published = !$document->is_published;
        $document->save();

        return redirect()->back()->with('success', 'Status publikasi berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $document = Document::findOrFail($id);
        
        if ($document->file_path && Storage::exists('public/' . $document->file_path)) {
            Storage::delete('public/' . $document->file_path);
        }
        
        $document->delete();

        return redirect()->route('admin.standard-service.index')->with('success', 'Dokumen standar layanan berhasil dihapus.');
    }
}
