<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Official;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OfficialController extends Controller
{
    public function index(Request $request)
    {
        return Official::query()
            ->when($request->has('is_published'), fn ($q) => $q->where('is_published', $request->boolean('is_published')))
            ->orderBy('order')->orderBy('name')
            ->get();
    }

    public function show(Official $official)
    {
        return $official;
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['order'] ??= 0;
        $data['is_published'] = $request->boolean('is_published', true);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('officials', 'public');
        }

        return response()->json(Official::create($data), 201);
    }

    public function update(Request $request, Official $official)
    {
        $data = $this->validated($request, true);

        if ($request->hasFile('photo')) {
            if ($official->photo) {
                Storage::disk('public')->delete($official->photo);
            }
            $data['photo'] = $request->file('photo')->store('officials', 'public');
        }

        $official->update($data);

        return $official;
    }

    public function destroy(Official $official)
    {
        if ($official->photo) {
            Storage::disk('public')->delete($official->photo);
        }
        $official->delete();

        return response()->noContent();
    }

    public function toggleStatus(Official $official)
    {
        $official->update(['is_published' => ! $official->is_published]);

        return $official;
    }

    private function validated(Request $request, bool $partial = false): array
    {
        $req = $partial ? 'sometimes|required' : 'required';

        return collect($request->validate([
            'name' => "$req|string|max:255",
            'position' => "$req|string|max:255",
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'bio' => 'nullable|string',
            'order' => 'nullable|integer|min:0',
            'is_published' => 'nullable|boolean',
        ]))->except('photo')->all();
    }
}
