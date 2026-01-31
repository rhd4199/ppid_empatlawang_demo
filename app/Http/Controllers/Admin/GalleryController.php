<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Models\GalleryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    public function index()
    {
        $galleries = Gallery::latest()->paginate(12);
        return view('admin.gallery.index', compact('galleries'));
    }

    public function create()
    {
        return view('admin.gallery.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cover_image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'is_published' => 'nullable|boolean',
        ]);

        $coverPath = $request->file('cover_image')->store('galleries/covers', 'public');

        $gallery = Gallery::create([
            'title' => $request->title,
            'description' => $request->description,
            'type' => 'photo',
            'cover_image' => $coverPath,
            'is_published' => $request->has('is_published'),
        ]);

        return redirect()->route('admin.galleries.edit', $gallery->id)->with('success', 'Album berhasil dibuat. Silakan tambahkan foto.');
    }

    public function edit($id)
    {
        $gallery = Gallery::with(['items' => function ($query) {
            $query->orderBy('order', 'asc');
        }])->findOrFail($id);
        
        return view('admin.gallery.edit', compact('gallery'));
    }

    public function update(Request $request, $id)
    {
        $gallery = Gallery::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'is_published' => 'nullable|boolean',
        ]);

        $data = [
            'title' => $request->title,
            'description' => $request->description,
            'is_published' => $request->has('is_published'),
        ];

        if ($request->hasFile('cover_image')) {
            // Delete old cover
            if ($gallery->cover_image) {
                Storage::disk('public')->delete($gallery->cover_image);
            }
            $data['cover_image'] = $request->file('cover_image')->store('galleries/covers', 'public');
        }

        $gallery->update($data);

        return redirect()->back()->with('success', 'Album berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $gallery = Gallery::findOrFail($id);

        // Delete all photos
        foreach ($gallery->items as $item) {
            Storage::disk('public')->delete($item->image_path);
            $item->delete();
        }

        // Delete cover
        if ($gallery->cover_image) {
            Storage::disk('public')->delete($gallery->cover_image);
        }

        $gallery->delete();

        return redirect()->route('admin.galleries.index')->with('success', 'Album berhasil dihapus.');
    }

    public function uploadPhotos(Request $request, $id)
    {
        $request->validate([
            'photos' => 'required|array',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240'
        ]);

        $gallery = Gallery::findOrFail($id);
        $count = $gallery->items()->count();

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $index => $photo) {
                $path = $photo->store('galleries/items', 'public');
                GalleryItem::create([
                    'gallery_id' => $gallery->id,
                    'image_path' => $path,
                    'order' => $count + $index + 1,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Foto berhasil ditambahkan.');
    }

    public function deletePhoto($id)
    {
        $item = GalleryItem::findOrFail($id);
        Storage::disk('public')->delete($item->image_path);
        $item->delete();

        return redirect()->back()->with('success', 'Foto berhasil dihapus.');
    }

    public function updatePhotoOrder(Request $request, $id)
    {
        // Expecting arrays: item_ids and orders
        $request->validate([
            'orders' => 'required|array',
            'orders.*' => 'integer'
        ]);

        foreach ($request->orders as $itemId => $order) {
            GalleryItem::where('id', $itemId)->where('gallery_id', $id)->update(['order' => $order]);
        }

        return redirect()->back()->with('success', 'Urutan foto berhasil diperbarui.');
    }
    
    public function toggleStatus($id)
    {
        $gallery = Gallery::findOrFail($id);
        $gallery->is_published = !$gallery->is_published;
        $gallery->save();

        return redirect()->back()->with('success', 'Status publikasi berhasil diubah.');
    }

    public function bulkAction(Request $request)
    {
        $action = $request->input('action');
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return redirect()->back()->with('error', 'Tidak ada item yang dipilih.');
        }

        switch ($action) {
            case 'publish':
                Gallery::whereIn('id', $ids)->update(['is_published' => true]);
                $message = 'Album yang dipilih berhasil dipublikasikan.';
                break;
            case 'unpublish':
                Gallery::whereIn('id', $ids)->update(['is_published' => false]);
                $message = 'Album yang dipilih berhasil di-unpublish.';
                break;
            case 'delete':
                $galleries = Gallery::whereIn('id', $ids)->get();
                foreach ($galleries as $gallery) {
                    // Delete all photos
                    foreach ($gallery->items as $item) {
                        Storage::disk('public')->delete($item->image_path);
                        $item->delete();
                    }
                    // Delete cover
                    if ($gallery->cover_image) {
                        Storage::disk('public')->delete($gallery->cover_image);
                    }
                    $gallery->delete();
                }
                $message = 'Album yang dipilih berhasil dihapus.';
                break;
            default:
                return redirect()->back()->with('error', 'Aksi tidak valid.');
        }

        return redirect()->back()->with('success', $message);
    }
}
