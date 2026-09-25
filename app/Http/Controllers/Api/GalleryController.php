<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Models\GalleryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['per_page' => 'nullable|integer|min:1|max:100']);

        return Gallery::query()
            ->withCount('items')
            ->when($request->has('is_published'), fn ($q) => $q->where('is_published', $request->boolean('is_published')))
            ->latest()
            ->paginate($request->integer('per_page', 12));
    }

    public function show(Gallery $gallery)
    {
        return $gallery->load(['items' => fn ($q) => $q->orderBy('order')]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cover_image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'is_published' => 'nullable|boolean',
        ]);

        $data['type'] = 'photo';
        $data['cover_image'] = $request->file('cover_image')->store('galleries/covers', 'public');
        $data['is_published'] = $request->boolean('is_published', true);

        return response()->json(Gallery::create($data), 201);
    }

    public function update(Request $request, Gallery $gallery)
    {
        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'is_published' => 'nullable|boolean',
        ]);

        unset($data['cover_image']);
        if ($request->hasFile('cover_image')) {
            if ($gallery->cover_image) {
                Storage::disk('public')->delete($gallery->cover_image);
            }
            $data['cover_image'] = $request->file('cover_image')->store('galleries/covers', 'public');
        }

        $gallery->update($data);

        return $gallery;
    }

    public function destroy(Gallery $gallery)
    {
        $this->purge($gallery);

        return response()->noContent();
    }

    public function toggleStatus(Gallery $gallery)
    {
        $gallery->update(['is_published' => ! $gallery->is_published]);

        return $gallery;
    }

    public function bulk(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:galleries,id',
            'action' => 'required|in:delete,publish,unpublish',
        ]);

        $query = Gallery::whereIn('id', $data['ids']);

        if ($data['action'] === 'delete') {
            $query->get()->each(fn ($g) => $this->purge($g));
        } else {
            $query->update(['is_published' => $data['action'] === 'publish']);
        }

        return response()->json(['affected' => count($data['ids'])]);
    }

    public function uploadPhotos(Request $request, Gallery $gallery)
    {
        $request->validate([
            'photos' => 'required|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'captions' => 'nullable|array',
            'captions.*' => 'nullable|string|max:255',
        ]);

        $order = $gallery->items()->max('order') ?? 0;
        $items = [];
        foreach ($request->file('photos') as $i => $photo) {
            $items[] = $gallery->items()->create([
                'image_path' => $photo->store('galleries/items', 'public'),
                'caption' => $request->input("captions.$i"),
                'order' => ++$order,
            ]);
        }

        return response()->json($items, 201);
    }

    public function updateOrder(Request $request, Gallery $gallery)
    {
        $data = $request->validate([
            'orders' => 'required|array', // { "<item_id>": <order>, ... }
            'orders.*' => 'integer',
        ]);

        foreach ($data['orders'] as $itemId => $order) {
            $gallery->items()->where('id', $itemId)->update(['order' => $order]);
        }

        return $gallery->load(['items' => fn ($q) => $q->orderBy('order')]);
    }

    public function deletePhoto(GalleryItem $item)
    {
        Storage::disk('public')->delete($item->image_path);
        $item->delete();

        return response()->noContent();
    }

    private function purge(Gallery $gallery): void
    {
        $paths = $gallery->items->pluck('image_path')->push($gallery->cover_image)->filter()->all();
        Storage::disk('public')->delete($paths);
        $gallery->items()->delete();
        $gallery->delete();
    }
}
