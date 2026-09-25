<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index()
    {
        return Profile::all();
    }

    public function show(string $slug)
    {
        return Profile::where('slug', $slug)->firstOrFail();
    }

    public function update(Request $request, string $slug)
    {
        $profile = Profile::where('slug', $slug)->firstOrFail();

        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        unset($data['image']);
        if ($request->hasFile('image')) {
            if ($profile->image) {
                Storage::disk('public')->delete($profile->image);
            }
            $data['image'] = $request->file('image')->store('profiles', 'public');
        }

        $profile->update($data);

        return $profile;
    }
}
