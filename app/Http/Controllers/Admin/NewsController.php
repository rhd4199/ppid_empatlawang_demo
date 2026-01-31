<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $sortField = $request->query('sort_field', 'created_at');
        $sortDirection = $request->query('sort_direction', 'desc');
        
        $allowedSorts = ['title', 'author', 'created_at', 'is_published', 'published_at'];
        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'created_at';
        }
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        $news = News::orderBy($sortField, $sortDirection)->paginate(12); // Pagination for cards
        return view('admin.news.index', compact('news'));
    }

    public function create()
    {
        return view('admin.news.create');
    }

    public function edit($id)
    {
        $news = News::findOrFail($id);
        return view('admin.news.edit', compact('news'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'author' => 'nullable|string|max:100',
            'is_published' => 'nullable|boolean',
            'published_at' => 'nullable|date',
        ]);

        $data = [
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . time(),
            'content' => $request->content,
            'author' => $request->author ?? 'Admin',
            'published_at' => $request->published_at ?? now(),
            'is_published' => $request->has('is_published') ? $request->is_published : true,
        ];

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('news', 'public');
            $data['image'] = $path;
        }

        News::create($data);

        return redirect()->route('admin.news.index')->with('success', 'Berita berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $news = News::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'author' => 'nullable|string|max:100',
            'is_published' => 'nullable|boolean',
            'published_at' => 'nullable|date',
        ]);

        $data = [
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . $news->id, // Keep ID to avoid collision or change logic
            'content' => $request->content,
            'author' => $request->author,
            'published_at' => $request->published_at,
            'is_published' => $request->has('is_published') ? $request->is_published : $news->is_published,
        ];

        if ($request->hasFile('image')) {
            // Delete old image
            if ($news->image && Storage::exists('public/' . $news->image)) {
                Storage::delete('public/' . $news->image);
            }
            
            $path = $request->file('image')->store('news', 'public');
            $data['image'] = $path;
        }

        $news->update($data);

        return redirect()->route('admin.news.index')->with('success', 'Berita berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $news = News::findOrFail($id);
        
        if ($news->image && Storage::exists('public/' . $news->image)) {
            Storage::delete('public/' . $news->image);
        }
        
        $news->delete();

        return redirect()->route('admin.news.index')->with('success', 'Berita berhasil dihapus.');
    }

    public function toggleStatus($id)
    {
        $news = News::findOrFail($id);
        $news->is_published = !$news->is_published;
        $news->save();

        return redirect()->route('admin.news.index')->with('success', 'Status berita berhasil diperbarui.');
    }

    public function toggleHeadline($id)
    {
        $news = News::findOrFail($id);

        if ($news->is_headline) {
            // If it is already headline, just untoggle it
            $news->update(['is_headline' => false]);
            $message = 'Berita tidak lagi menjadi Berita Utama.';
        } else {
            // If it is not headline, set it as headline and unset others
            News::where('is_headline', true)->update(['is_headline' => false]);
            $news->update(['is_headline' => true]);
            $message = 'Berita berhasil dijadikan Berita Utama.';
        }

        return redirect()->back()->with('success', $message);
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:news,id',
            'action' => 'required|in:delete,publish,unpublish'
        ]);

        $ids = $request->ids;
        $action = $request->action;
        $count = count($ids);

        if ($action === 'delete') {
            $newsItems = News::whereIn('id', $ids)->get();
            foreach ($newsItems as $news) {
                if ($news->image && Storage::exists('public/' . $news->image)) {
                    Storage::delete('public/' . $news->image);
                }
                $news->delete();
            }
            return redirect()->route('admin.news.index')->with('success', "$count berita berhasil dihapus.");
        } elseif ($action === 'publish') {
            News::whereIn('id', $ids)->update(['is_published' => true]);
            return redirect()->route('admin.news.index')->with('success', "$count berita berhasil dipublikasikan.");
        } elseif ($action === 'unpublish') {
            News::whereIn('id', $ids)->update(['is_published' => false]);
            return redirect()->route('admin.news.index')->with('success', "$count berita berhasil ditarik dari publikasi.");
        }

        return redirect()->route('admin.news.index');
    }
}
