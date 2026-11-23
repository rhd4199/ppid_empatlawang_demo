<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\Official;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/** /admin/instansi — perangkat daerah (OPD) shown on /instansi and used by Sapa Madani (tool kontak_instansi). */
class AgencyController extends Controller
{
    public function index(Request $request)
    {
        $sortField = in_array($request->query('sort_field'), ['name', 'category', 'confidence', 'checked_at', 'order'], true)
            ? $request->query('sort_field') : 'order';
        $sortDirection = $request->query('sort_direction') === 'desc' ? 'desc' : 'asc';

        $items = Agency::with('official')->search($request->query('q'))
            ->when(isset(Agency::CATEGORIES[(string) $request->query('kategori')]), fn ($q) => $q->where('category', $request->query('kategori')))
            ->orderBy($sortField, $sortDirection)->orderBy('name')
            ->paginate(25)->withQueryString();

        return view('admin.agency.index', ['items' => $items]);
    }

    public function create()
    {
        return view('admin.agency.create', ['item' => new Agency(['is_published' => true, 'category' => 'dinas', 'address_verified' => true, 'checked_at' => now()]), 'officials' => $this->officials()]);
    }

    public function store(Request $request)
    {
        Agency::create($this->validated($request));

        return redirect()->route('admin.agencies.index')->with('success', 'Instansi berhasil ditambahkan.');
    }

    public function edit($id)
    {
        return view('admin.agency.edit', ['item' => Agency::findOrFail($id), 'officials' => $this->officials()]);
    }

    public function update(Request $request, $id)
    {
        $item = Agency::findOrFail($id);
        $item->update($this->validated($request, $item));

        return redirect()->route('admin.agencies.index')->with('success', 'Instansi berhasil diperbarui.');
    }

    public function toggleStatus($id)
    {
        $item = Agency::findOrFail($id);
        $item->update(['is_published' => ! $item->is_published]);

        return redirect()->back()->with('success', 'Status tampil instansi berhasil diperbarui.');
    }

    public function destroy($id)
    {
        Agency::findOrFail($id)->delete();

        return redirect()->route('admin.agencies.index')->with('success', 'Instansi berhasil dihapus.');
    }

    private function officials()
    {
        return Official::orderBy('position')->get(['id', 'name', 'position', 'is_published']);
    }

    private function validated(Request $request, ?Agency $item = null): array
    {
        $request->merge(['slug' => Str::slug((string) ($request->input('slug') ?: $request->input('name')))]);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:50',
            'slug' => ['required', 'string', 'max:120', Rule::unique('agencies', 'slug')->ignore($item?->id)],
            'category' => ['required', Rule::in(array_keys(Agency::CATEGORIES))],
            'keywords' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:3000',
            'address' => 'nullable|string|max:500',
            'phones_text' => 'nullable|string|max:2000',
            'emails_text' => 'nullable|string|max:2000',
            'website' => 'nullable|url:http,https|max:255',
            'social' => 'nullable|array',
            'social.*' => 'nullable|url:http,https|max:255',
            'service_hours' => 'nullable|string|max:255',
            'head_name' => 'nullable|string|max:255',
            'official_id' => 'nullable|integer|exists:officials,id',
            'latitude' => 'nullable|numeric|between:-11,6|required_with:longitude',
            'longitude' => 'nullable|numeric|between:95,141|required_with:latitude',
            'sources_text' => 'nullable|string|max:5000',
            'confidence' => ['nullable', Rule::in(array_keys(Agency::CONFIDENCE))],
            'checked_at' => 'nullable|date|before_or_equal:today',
            'order' => 'nullable|integer|min:0|max:9999',
        ]);
        $data['phones'] = self::parseLines($data['phones_text'] ?? '', fn ($value, $label, $verified) => ['number' => mb_substr($value, 0, 40), 'label' => $label, 'verified' => $verified]);
        $data['emails'] = self::parseLines($data['emails_text'] ?? '', fn ($value, $label, $verified) => ['address' => mb_substr($value, 0, 120), 'verified' => $verified]);
        $old = collect($item?->sources ?? [])->keyBy('url');
        $data['sources'] = self::parseLines($data['sources_text'] ?? '', fn ($url, $title) => array_filter(array_merge($old[$url] ?? [], ['url' => $url, 'title' => $title ?? ($old[$url]['title'] ?? null)])));
        $bad = collect($data['emails'])->first(fn ($e) => ! filter_var($e['address'], FILTER_VALIDATE_EMAIL));
        if ($bad) {
            throw ValidationException::withMessages(['emails_text' => "Email tidak valid: {$bad['address']}"]);
        }
        $data['social'] = array_intersect_key(array_filter($data['social'] ?? []), Agency::SOCIAL) ?: null;
        $data['order'] = (int) ($data['order'] ?? 0);
        $data['address_verified'] = $request->boolean('address_verified');
        $data['is_published'] = $request->boolean('is_published');
        unset($data['phones_text'], $data['emails_text'], $data['sources_text']);

        return $data;
    }

    /** One entry per line: "value | label"; a leading "?" = one source only (stored hidden). */
    public static function parseLines(string $text, callable $make): array
    {
        $rows = [];
        foreach (preg_split('/\R/', $text) as $line) {
            $line = trim($line);
            $verified = ! str_starts_with($line, '?');
            [$value, $label] = array_map('trim', explode('|', ltrim($line, '? '), 2)) + [1 => ''];
            if ($value !== '') {
                $rows[] = $make($value, $label !== '' ? mb_substr($label, 0, 100) : null, $verified);
            }
        }

        return $rows;
    }

    /** Inverse of parseLines() for the form. */
    public static function lines(?array $rows, string $valueKey, ?string $labelKey = null): string
    {
        return collect($rows ?? [])->map(fn ($r) => (($r['verified'] ?? true) ? '' : '? ') . ($r[$valueKey] ?? '')
            . ($labelKey && filled($r[$labelKey] ?? null) ? ' | ' . $r[$labelKey] : ''))->implode("\n");
    }
}
