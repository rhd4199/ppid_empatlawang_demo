<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Informasi Publik, Standar Layanan, Laporan and Pengadaan all live in the
 * documents table and only differ by category, so one endpoint serves them.
 */
class DocumentController extends Controller
{
    public const CATEGORIES = [
        'informasi-publik-berkala', 'informasi-publik-serta-merta',
        'informasi-publik-setiap-saat', 'informasi-publik-dikecualikan',
        'standar_layanan_alur', 'standar_layanan_tata_cara', 'standar_layanan_permohonan',
        'standar_layanan_keberatan', 'standar_layanan_sengketa', 'standar_layanan_sop',
        'standar_layanan_maklumat', 'standar_layanan_biaya',
        'laporan_pemda', 'laporan_ppid',
        'pengadaan_info', 'pengadaan_regulasi',
    ];

    public function index(Request $request)
    {
        $request->validate([
            'category' => 'nullable|string',
            'sort_field' => 'nullable|in:title,category,created_at,is_published',
            'sort_direction' => 'nullable|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        return Document::query()
            // comma-separated list, or a prefix group like "informasi-publik" / "laporan"
            ->when($request->category, function ($q, $cat) {
                $cats = explode(',', $cat);
                $q->where(function ($q) use ($cats) {
                    foreach ($cats as $c) {
                        $q->orWhere('category', 'like', $c.'%');
                    }
                });
            })
            ->when($request->has('is_published'), fn ($q) => $q->where('is_published', $request->boolean('is_published')))
            ->when($request->search, fn ($q, $s) => $q->where(fn ($q) => $q
                ->where('title', 'like', "%$s%")->orWhere('description', 'like', "%$s%")))
            ->orderBy($request->input('sort_field', 'created_at'), $request->input('sort_direction', 'desc'))
            ->paginate($request->integer('per_page', 15));
    }

    public function show(Document $document)
    {
        return $document;
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['is_published'] = $request->boolean('is_published', true);

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('documents', 'public');
        }

        return response()->json(Document::create($data), 201);
    }

    public function update(Request $request, Document $document)
    {
        $data = $this->validated($request, $document);

        if ($request->hasFile('file')) {
            $this->deleteFile($document);
            $data['file_path'] = $request->file('file')->store('documents', 'public');
        }

        $document->update($data);

        return $document;
    }

    public function destroy(Document $document)
    {
        $this->deleteFile($document);
        $document->delete();

        return response()->noContent();
    }

    public function toggleStatus(Document $document)
    {
        $document->update(['is_published' => ! $document->is_published]);

        return $document;
    }

    public function bulk(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:documents,id',
            'action' => 'required|in:delete,publish,unpublish',
        ]);

        $query = Document::whereIn('id', $data['ids']);

        if ($data['action'] === 'delete') {
            $query->get()->each(function ($d) {
                $this->deleteFile($d);
                $d->delete();
            });
        } else {
            $query->update(['is_published' => $data['action'] === 'publish']);
        }

        return response()->json(['affected' => count($data['ids'])]);
    }

    private function validated(Request $request, ?Document $document = null): array
    {
        $req = $document ? 'sometimes|required' : 'required';

        return collect($request->validate([
            'title' => "$req|string|max:255",
            'category' => [$document ? 'sometimes' : 'required', Rule::in(self::CATEGORIES)],
            'file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,csv,jpg,png|max:10240',
            'description' => 'nullable|string',
            'external_url' => 'nullable|string|max:255',
            'is_published' => 'nullable|boolean',
        ]))->except('file')->all();
    }

    private function deleteFile(Document $document): void
    {
        if ($document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }
    }
}
