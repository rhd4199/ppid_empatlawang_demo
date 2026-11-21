<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Models\GalleryItem;
use App\Support\RemoteImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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

    // A known source_url (repeated push by a cron/scraper) updates that album → 200 instead of a duplicate.
    public function store(Request $request)
    {
        $source = trim((string) $request->input('source_url'));
        if ($source !== '' && $existing = Gallery::where('source_url', $source)->first()) {
            return $this->update($request, $existing);
        }
        $data = $request->validate([
            'source_url' => 'nullable|string|max:500|unique:galleries,source_url',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            // a video album may skip the cover: the YouTube thumbnail is used
            'cover_image' => [Rule::requiredIf(fn () => $request->input('type') !== 'video' && ! $request->filled('cover_image_url')), 'nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'],
            'cover_image_url' => [Rule::requiredIf(fn () => $request->input('type') !== 'video' && ! $request->hasFile('cover_image')), 'nullable', 'url:http,https', 'max:2048'],
            'is_published' => 'nullable|boolean',
            ...self::PHOTO_URL_RULES,
            ...self::VIDEO_RULES,
        ]);
        $this->assertVideo($data['type'] ?? 'photo', $data['url'] ?? null);

        // Files first (no DB yet): a failed download deletes what was already stored, nothing half-created stays behind.
        $gallery = $this->withStoredFiles(function (array &$stored) use ($request, $data) {
            $stored[] = $cover = $this->storeCover($request);
            $photos = $this->downloadPhotos($data['photos'] ?? [], $stored);

            return DB::transaction(function () use ($data, $cover, $photos) {
                $gallery = Gallery::create([
                    'title' => $data['title'],
                    'description' => $data['description'] ?? null,
                    'source_url' => $data['source_url'] ?? null,
                    'type' => $data['type'] ?? 'photo',
                    'url' => ($data['type'] ?? 'photo') === 'video' ? $data['url'] : null,
                    'cover_image' => $cover,
                    'is_published' => (bool) ($data['is_published'] ?? true),
                ]);
                $this->addItems($gallery, $photos);

                return $gallery;
            });
        });

        return response()->json($gallery->load(['items' => fn ($q) => $q->orderBy('order')]), 201);
    }

    public function update(Request $request, Gallery $gallery)
    {
        $data = $request->validate([
            'source_url' => ['nullable', 'string', 'max:500', Rule::unique('galleries', 'source_url')->ignore($gallery->id)],
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'cover_image_url' => 'nullable|url:http,https|max:2048',
            'is_published' => 'nullable|boolean',
            ...self::PHOTO_URL_RULES,
            ...self::VIDEO_RULES,
        ]);
        $this->assertVideo($data['type'] ?? $gallery->type, $data['url'] ?? $gallery->url);
        $photos = $this->newPhotos($gallery, $data['photos'] ?? []);
        $fields = collect($data)->except(['cover_image', 'cover_image_url', 'photos'])->all();
        if (($fields['type'] ?? null) === 'photo') {
            $fields['url'] = null; // back to a photo album: drop the video link
        }
        $oldCover = $gallery->cover_image;

        $replaced = $this->withStoredFiles(function (array &$stored) use ($request, $gallery, $photos, $fields) {
            if ($cover = $this->storeCover($request)) {
                $stored[] = $fields['cover_image'] = $cover;
            }
            $downloaded = $this->downloadPhotos($photos, $stored);
            DB::transaction(function () use ($gallery, $fields, $downloaded) {
                $gallery->update($fields);
                $this->addItems($gallery, $downloaded);
            });

            return isset($fields['cover_image']);
        });
        if ($replaced && $oldCover) {
            Storage::disk('public')->delete($oldCover);
        }

        return $gallery->load(['items' => fn ($q) => $q->orderBy('order')]);
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
        // JSON { "photos": [{ "url": ..., "caption": ... }] } → download; multipart photos[] files → as before
        if (! $request->hasFile('photos')) {
            $data = $request->validate(['photos' => 'required|array'] + self::PHOTO_URL_RULES);
            $items = $this->withStoredFiles(function (array &$stored) use ($gallery, $data) {
                $downloaded = $this->downloadPhotos($this->newPhotos($gallery, $data['photos']), $stored);

                return DB::transaction(fn () => $this->addItems($gallery, $downloaded));
            });

            return response()->json($items, 201);
        }

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

    /** type=video needs a YouTube link (checked with Gallery::youtubeId). */
    private const VIDEO_RULES = [
        'type' => 'nullable|in:photo,video',
        'url' => 'nullable|required_if:type,video|string|max:500',
    ];

    private function assertVideo(string $type, ?string $url): void
    {
        if ($type === 'video' && ! Gallery::youtubeId($url)) {
            throw ValidationException::withMessages(['url' => 'Album video wajib berisi link video YouTube (youtube.com/watch?v=…, youtu.be/…, shorts/…).']);
        }
    }

    private const PHOTO_URL_RULES = [
        'photos' => 'nullable|array|max:50',
        'photos.*.url' => 'required|url:http,https|max:2048',
        'photos.*.caption' => 'nullable|string|max:255',
    ];

    /** Runs $work(&$stored); on any exception the files listed in $stored are deleted before rethrowing. */
    private function withStoredFiles(callable $work)
    {
        $stored = [];
        try {
            return $work($stored);
        } catch (\Throwable $e) {
            Storage::disk('public')->delete(array_filter($stored));
            throw $e;
        }
    }

    /** Uploaded file wins over cover_image_url; null when neither is sent. */
    private function storeCover(Request $request): ?string
    {
        if ($request->hasFile('cover_image')) {
            return $request->file('cover_image')->store('galleries/covers', 'public');
        }

        return $request->filled('cover_image_url')
            ? RemoteImage::store($request->input('cover_image_url'), 'galleries/covers', 'cover_image_url')
            : null;
    }

    /** Photos whose URL the album does not have yet (a re-sent album must not double its photos). */
    private function newPhotos(Gallery $gallery, array $photos): array
    {
        $known = $gallery->items()->whereNotNull('source_url')->pluck('source_url')->all();

        return collect($photos)->unique('url')->reject(fn ($p) => in_array($p['url'], $known, true))->values()->all();
    }

    /** @return array<int, array{image_path: string, caption: ?string, source_url: string}> */
    private function downloadPhotos(array $photos, array &$stored): array
    {
        $out = [];
        foreach (array_values($photos) as $i => $photo) {
            $stored[] = $path = RemoteImage::store($photo['url'], 'galleries/items', "photos.$i.url");
            $out[] = ['image_path' => $path, 'caption' => $photo['caption'] ?? null, 'source_url' => $photo['url']];
        }

        return $out;
    }

    private function addItems(Gallery $gallery, array $photos): array
    {
        $order = $gallery->items()->max('order') ?? 0;
        $items = [];
        foreach ($photos as $photo) {
            $items[] = $gallery->items()->create($photo + ['order' => ++$order]);
        }

        return $items;
    }

    private function purge(Gallery $gallery): void
    {
        $paths = $gallery->items->pluck('image_path')->push($gallery->cover_image)->filter()->all();
        DB::transaction(function () use ($gallery) {
            $gallery->items()->delete();
            $gallery->delete();
        });
        Storage::disk('public')->delete($paths);
    }
}
