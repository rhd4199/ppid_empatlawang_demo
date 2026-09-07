<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Official;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OfficialController extends Controller
{
    public function index()
    {
        $officials = Official::orderBy('order')->orderBy('name')->get();

        return view('admin.official.index', compact('officials'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'bio' => 'nullable|string',
            'order' => 'nullable|integer|min:0',
            'is_published' => 'nullable|boolean',
        ]);

        $path = null;
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('officials', 'public');
        }

        Official::create([
            'name' => $request->name,
            'position' => $request->position,
            'photo' => $path,
            'bio' => $request->bio,
            'order' => $request->order ?? 0,
            'is_published' => $request->has('is_published') ? $request->is_published : true,
        ]);

        return redirect()->route('admin.officials.index')->with('success', 'Pejabat berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $official = Official::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'bio' => 'nullable|string',
            'order' => 'nullable|integer|min:0',
            'is_published' => 'nullable|boolean',
        ]);

        $data = [
            'name' => $request->name,
            'position' => $request->position,
            'bio' => $request->bio,
            'order' => $request->order ?? 0,
            'is_published' => $request->has('is_published') ? $request->is_published : $official->is_published,
        ];

        if ($request->hasFile('photo')) {
            if ($official->photo) {
                Storage::disk('public')->delete($official->photo);
            }
            $data['photo'] = $request->file('photo')->store('officials', 'public');
        }

        $official->update($data);

        return redirect()->route('admin.officials.index')->with('success', 'Data pejabat berhasil diperbarui.');
    }

    public function toggleStatus($id)
    {
        $official = Official::findOrFail($id);
        $official->is_published = ! $official->is_published;
        $official->save();

        return redirect()->back()->with('success', 'Status publikasi berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $official = Official::findOrFail($id);

        if ($official->photo) {
            Storage::disk('public')->delete($official->photo);
        }

        $official->delete();

        return redirect()->route('admin.officials.index')->with('success', 'Data pejabat berhasil dihapus.');
    }
}
