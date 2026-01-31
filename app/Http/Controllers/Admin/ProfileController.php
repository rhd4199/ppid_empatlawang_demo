<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit($slug)
    {
        $profile = Profile::where('slug', $slug)->firstOrFail();
        return view('admin.profile.edit', compact('profile'));
    }

    public function update(Request $request, $id)
    {
        $profile = Profile::findOrFail($id);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        $data = [
            'title' => $request->title,
            'content' => $request->content,
        ];

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($profile->image && Storage::exists('public/' . $profile->image)) {
                Storage::delete('public/' . $profile->image);
            }
            
            $path = $request->file('image')->store('profiles', 'public');
            $data['image'] = $path;
        }

        $profile->update($data);

        return redirect()->back()->with('status', 'Profil berhasil diperbarui!');
    }
}
