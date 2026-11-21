<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequirement;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceRequirementController extends Controller
{
    public function index(Request $request)
    {
        $sortField = $request->query('sort_field', 'created_at');
        $sortDirection = $request->query('sort_direction', 'desc');

        if (!in_array($sortField, ['title', 'category', 'agency', 'checked_at', 'created_at', 'is_published'])) {
            $sortField = 'created_at';
        }
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        $query = ServiceRequirement::search($request->query('q'));
        if ($category = $request->query('category')) {
            $query->where('category', $category);
        }

        $items = $query->orderBy($sortField, $sortDirection)->paginate(25)->withQueryString();

        return view('admin.service_requirement.index', [
            'items' => $items,
            'categories' => ServiceRequirement::CATEGORIES,
        ]);
    }

    public function create()
    {
        return view('admin.service_requirement.create', ['item' => new ServiceRequirement(['is_published' => true])]);
    }

    public function store(Request $request)
    {
        ServiceRequirement::create($this->validated($request));

        return redirect()->route('admin.service-requirements.index')->with('success', 'Persyaratan layanan berhasil ditambahkan.');
    }

    public function edit($id)
    {
        return view('admin.service_requirement.edit', ['item' => ServiceRequirement::findOrFail($id)]);
    }

    public function update(Request $request, $id)
    {
        $item = ServiceRequirement::findOrFail($id);
        $data = $this->validated($request, $item);
        $data['slug'] = $data['slug'] ?: $item->slug;
        $item->update($data);

        return redirect()->route('admin.service-requirements.index')->with('success', 'Persyaratan layanan berhasil diperbarui.');
    }

    public function toggleStatus($id)
    {
        $item = ServiceRequirement::findOrFail($id);
        $item->update(['is_published' => !$item->is_published]);

        return redirect()->back()->with('success', 'Status publikasi berhasil diperbarui.');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:service_requirements,id',
            'action' => 'required|in:delete,publish,unpublish',
        ]);

        $query = ServiceRequirement::whereIn('id', $request->ids);
        $count = count($request->ids);

        if ($request->action === 'delete') {
            $query->delete();
            $message = "$count layanan berhasil dihapus.";
        } else {
            $query->update(['is_published' => $request->action === 'publish']);
            $message = $request->action === 'publish'
                ? "$count layanan berhasil dipublikasikan."
                : "$count layanan berhasil ditarik dari publikasi.";
        }

        return redirect()->route('admin.service-requirements.index')->with('success', $message);
    }

    public function destroy($id)
    {
        ServiceRequirement::findOrFail($id)->delete();

        return redirect()->route('admin.service-requirements.index')->with('success', 'Persyaratan layanan berhasil dihapus.');
    }

    private function validated(Request $request, ?ServiceRequirement $item = null): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => ['nullable', 'alpha_dash', 'max:255', Rule::unique('service_requirements', 'slug')->ignore($item?->id)],
            'category' => ['required', Rule::in(array_keys(ServiceRequirement::CATEGORIES))],
            'agency' => 'required|string|max:255',
            'requirements' => 'required|string',
            'steps' => 'nullable|string',
            'cost' => 'nullable|string|max:2000',
            'duration' => 'nullable|string|max:2000',
            'legal_basis' => 'nullable|string',
            'source_url' => 'nullable|url:http,https|max:500',
            'checked_at' => 'nullable|date|before_or_equal:today',
            'keywords' => 'nullable|string|max:2000',
        ]);
        $data['is_published'] = $request->boolean('is_published');

        return $data;
    }
}
