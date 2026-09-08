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
            // same rule as Informasi Publik: published unless explicitly unticked
            'is_published' => $request->has('is_published') ? $request->is_published : true,
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
            'is_published' => $request->has('is_published') ? $request->is_published : $gallery->is_published,
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

    /**
     * Chunked upload: the browser slices each photo into pieces small enough to
     * pass upload_max_filesize / post_max_size, we append them and only then
     * push the assembled file to S3.
     */
    public function uploadChunk(Request $request, $id)
    {
        $data = $request->validate([
            'upload_id' => ['required', 'regex:/^[A-Za-z0-9_-]{8,64}$/'],
            'index' => 'required|integer|min:0',
            'total' => 'required|integer|min:1|max:5000',
            'chunk' => 'required|file|max:4096',
        ]);

        $gallery = Gallery::findOrFail($id);

        $dir = storage_path('app/chunks');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $tmp = $dir.'/'.$data['upload_id'];

        // ponytail: abandoned uploads leak temp files, so sweep stale ones here
        // instead of adding a scheduled command for it
        foreach (glob($dir.'/*') ?: [] as $stale) {
            if (filemtime($stale) < time() - 6 * 3600) {
                @unlink($stale);
            }
        }

        // First chunk starts a fresh file; a retried upload must not append twice.
        if ((int) $data['index'] === 0) {
            @unlink($tmp);
        } elseif (! is_file($tmp)) {
            return response()->json(['message' => 'Potongan file hilang, silakan ulangi upload.'], 422);
        }

        file_put_contents($tmp, file_get_contents($request->file('chunk')->getRealPath()), FILE_APPEND);

        if ((int) $data['index'] + 1 < (int) $data['total']) {
            return response()->json(['status' => 'chunk-received']);
        }

        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        ];
        $mime = mime_content_type($tmp);

        if (! isset($allowed[$mime]) || filesize($tmp) > 20 * 1024 * 1024) {
            @unlink($tmp);

            return response()->json(['message' => 'File harus gambar (jpg, png, gif, webp) maksimal 20MB.'], 422);
        }

        $path = 'galleries/items/'.\Illuminate\Support\Str::random(40).'.'.$allowed[$mime];
        $stream = fopen($tmp, 'r');
        Storage::disk('public')->put($path, $stream, 'public');
        if (is_resource($stream)) {
            fclose($stream);
        }
        @unlink($tmp);

        $item = GalleryItem::create([
            'gallery_id' => $gallery->id,
            'image_path' => $path,
            'order' => $gallery->items()->count() + 1,
        ]);

        return response()->json(['id' => $item->id, 'url' => storage_url($path)]);
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
