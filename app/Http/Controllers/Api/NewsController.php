<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'sort_field' => 'nullable|in:title,author,created_at,is_published,published_at',
            'sort_direction' => 'nullable|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        return News::query()
            ->when($request->has('is_published'), fn ($q) => $q->where('is_published', $request->boolean('is_published')))
            ->when($request->has('is_headline'), fn ($q) => $q->where('is_headline', $request->boolean('is_headline')))
            ->when($request->search, fn ($q, $s) => $q->where('title', 'like', "%$s%"))
            ->orderBy($request->input('sort_field', 'created_at'), $request->input('sort_direction', 'desc'))
            ->paginate($request->integer('per_page', 15));
    }

    public function show(News $news)
    {
        return $news;
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = Str::slug($data['title']).'-'.time();
        $data['author'] ??= 'Admin';
        $data['published_at'] ??= now();
        $data['is_published'] = $request->boolean('is_published', true);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('news', 'public');
        }

        return response()->json(News::create($data), 201);
    }

    public function update(Request $request, News $news)
    {
        $data = $this->validated($request, true);

        if (isset($data['title'])) {
            $data['slug'] = Str::slug($data['title']).'-'.$news->id;
        }

        if ($request->hasFile('image')) {
            $this->deleteImage($news);
            $data['image'] = $request->file('image')->store('news', 'public');
        }

        $news->update($data);

        return $news;
    }

    public function destroy(News $news)
    {
        $this->deleteImage($news);
        $news->delete();

        return response()->noContent();
    }

    public function toggleStatus(News $news)
    {
        $news->update(['is_published' => ! $news->is_published]);

        return $news;
    }

    // Only one headline at a time.
    public function toggleHeadline(News $news)
    {
        if (! $news->is_headline) {
            News::where('is_headline', true)->update(['is_headline' => false]);
        }
        $news->update(['is_headline' => ! $news->is_headline]);

        return $news;
    }

    public function bulk(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:news,id',
            'action' => 'required|in:delete,publish,unpublish',
        ]);

        $query = News::whereIn('id', $data['ids']);

        if ($data['action'] === 'delete') {
            $query->get()->each(function ($n) {
                $this->deleteImage($n);
                $n->delete();
            });
        } else {
            $query->update(['is_published' => $data['action'] === 'publish']);
        }

        return response()->json(['affected' => count($data['ids'])]);
    }

    private function validated(Request $request, bool $partial = false): array
    {
        $req = $partial ? 'sometimes|required' : 'required';

        return collect($request->validate([
            'title' => "$req|string|max:255",
            'content' => "$req|string",
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'author' => 'nullable|string|max:100',
            'is_published' => 'nullable|boolean',
            'published_at' => 'nullable|date',
        ]))->except('image')->all();
    }

    private function deleteImage(News $news): void
    {
        if ($news->image) {
            Storage::disk('public')->delete($news->image);
        }
    }
}
