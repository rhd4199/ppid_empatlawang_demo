<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SppgUnit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SppgUnitController extends Controller
{
    public function index(Request $request)
    {
        $sortField = in_array($request->query('sort_field'), ['name', 'kecamatan', 'status', 'institutions_count', 'checked_at'], true)
            ? $request->query('sort_field') : 'kecamatan';
        $sortDirection = $request->query('sort_direction') === 'desc' ? 'desc' : 'asc';

        $items = SppgUnit::search($request->query('q'))
            ->when($request->query('kecamatan'), fn ($q, $kec) => $q->where('kecamatan', $kec))
            ->orderBy($sortField, $sortDirection)->orderBy('name')
            ->paginate(25)->withQueryString();

        return view('admin.sppg.index', [
            'items' => $items,
            'kecamatans' => SppgUnit::query()->distinct()->orderBy('kecamatan')->pluck('kecamatan'),
        ]);
    }

    public function create()
    {
        return view('admin.sppg.create', ['item' => new SppgUnit(['is_published' => true, 'status' => 'operasional'])]);
    }

    public function store(Request $request)
    {
        SppgUnit::create($this->validated($request));

        return redirect()->route('admin.sppg.index')->with('success', 'SPPG berhasil ditambahkan.');
    }

    public function edit($id)
    {
        return view('admin.sppg.edit', ['item' => SppgUnit::findOrFail($id)]);
    }

    public function update(Request $request, $id)
    {
        $item = SppgUnit::findOrFail($id);
        $item->update($this->validated($request, $item));

        return redirect()->route('admin.sppg.index')->with('success', 'SPPG berhasil diperbarui.');
    }

    public function toggleStatus($id)
    {
        $item = SppgUnit::findOrFail($id);
        $item->update(['is_published' => ! $item->is_published]);

        return redirect()->back()->with('success', 'Status tampil SPPG berhasil diperbarui.');
    }

    public function destroy($id)
    {
        SppgUnit::findOrFail($id)->delete();

        return redirect()->route('admin.sppg.index')->with('success', 'SPPG berhasil dihapus.');
    }

    private function validated(Request $request, ?SppgUnit $item = null): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'bgn_id' => ['nullable', 'string', 'max:20', Rule::unique('sppg_units', 'bgn_id')->ignore($item?->id)],
            'code' => 'nullable|string|max:30',
            'kecamatan' => 'required|string|max:100',
            'village' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'operator' => 'nullable|string|max:255',
            'status' => ['required', Rule::in(array_keys(SppgUnit::STATUSES))],
            'beneficiaries' => 'nullable|integer|min:0|max:100000',
            'institutions_text' => 'nullable|string|max:30000',
            'latitude' => 'nullable|numeric|between:-11,6|required_with:longitude',
            'longitude' => 'nullable|numeric|between:95,141|required_with:latitude',
            'notes' => 'nullable|string|max:2000',
            'source_url' => 'nullable|url:http,https|max:500',
            'checked_at' => 'nullable|date|before_or_equal:today',
        ]);
        $data['institutions'] = self::parseInstitutions($data['institutions_text'] ?? '');
        unset($data['institutions_text']);
        $data['is_published'] = $request->boolean('is_published');

        return $data;
    }

    /** One served institution per line: "Nama | Desa | Kecamatan" (desa and kecamatan optional). */
    public static function parseInstitutions(string $text): array
    {
        $rows = [];
        foreach (preg_split('/\R/', $text) as $line) {
            $parts = array_map('trim', explode('|', $line));
            if ($parts[0] !== '') {
                $rows[] = ['name' => mb_substr($parts[0], 0, 200), 'village' => ($parts[1] ?? '') ?: null, 'kecamatan' => ($parts[2] ?? '') ?: null];
            }
        }

        return $rows;
    }

    /** Inverse of parseInstitutions() for the form. */
    public static function institutionsText(?array $rows): string
    {
        return collect($rows ?? [])->map(fn ($r) => rtrim(implode(' | ', [$r['name'] ?? '', $r['village'] ?? '', $r['kecamatan'] ?? '']), ' |'))->implode("\n");
    }
}
