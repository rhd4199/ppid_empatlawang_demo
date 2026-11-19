<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InfoPublicController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Document::query();
        
        // Filter only public information categories
        $query->whereIn('category', [
            'informasi-publik-berkala',
            'informasi-publik-serta-merta',
            'informasi-publik-setiap-saat',
            'informasi-publik-dikecualikan'
        ])->where('is_published', true);

        // Handle category filter
        if ($request->has('category') && $request->category != '') {
            $categoryMap = [
                'berkala' => 'informasi-publik-berkala',
                'serta-merta' => 'informasi-publik-serta-merta',
                'setiap-saat' => 'informasi-publik-setiap-saat',
                'dikecualikan' => 'informasi-publik-dikecualikan',
            ];

            if (array_key_exists($request->category, $categoryMap)) {
                $query->where('category', $categoryMap[$request->category]);
            }
        }

        // Case-insensitive on every driver (Postgres LIKE is case-sensitive); % and _ typed by the user match literally.
        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $term = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower(mb_substr($search, 0, 100))) . '%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw("LOWER(title) LIKE ? ESCAPE '!'", [$term])
                  ->orWhereRaw("LOWER(description) LIKE ? ESCAPE '!'", [$term]);
            });
        }

        $infos = $query->latest()->paginate(10)->withQueryString();

        if ($request->ajax()) {
            return view('info_public._list', compact('infos'))->render();
        }

        return view('info_public.index', compact('infos'));
    }

    /** Force a file download (also for S3, where the HTML download attribute is ignored cross-origin). */
    public function download($id)
    {
        $info = Document::where('is_published', true)->whereNotNull('file_path')->findOrFail($id);
        abort_unless(Storage::disk('public')->exists($info->file_path), 404);

        $info->increment('download_count');
        $name = Str::slug(Str::limit($info->title, 80, '')) . '.' . (pathinfo($info->file_path, PATHINFO_EXTENSION) ?: 'pdf');

        return Storage::disk('public')->download($info->file_path, $name);
    }

    /**
     * Detail page — gives every checklist item a stable, linkable URL
     * (needed for the Monev/self-assessment form, which asks for a link
     * per item, not just a category listing).
     */
    public function show($id)
    {
        $info = Document::where('is_published', true)->findOrFail($id);

        return view('info_public.show', ['info' => $info]);
    }
}
