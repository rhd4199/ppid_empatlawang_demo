<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequirement;
use Illuminate\Http\Request;

class ServiceRequirementController extends Controller
{
    public function index(Request $request)
    {
        $categories = ServiceRequirement::CATEGORIES;
        $category = isset($categories[(string) $request->query('kategori')]) ? (string) $request->query('kategori') : null;
        $q = trim((string) $request->query('q'));

        $items = ServiceRequirement::where('is_published', true)
            ->search($q)
            ->when($category, fn ($query) => $query->where('category', $category))
            ->orderBy('title')
            ->get(['id', 'title', 'slug', 'category', 'agency', 'cost', 'checked_at']);

        // Groups follow the CATEGORIES order, not the alphabet.
        $groups = collect($categories)
            ->map(fn ($label, $key) => ['label' => $label, 'items' => $items->where('category', $key)])
            ->filter(fn ($group) => $group['items']->isNotEmpty());

        return view('service_requirement.index', compact('groups', 'categories', 'category', 'q') + ['total' => $items->count()]);
    }

    public function show(string $slug)
    {
        $item = ServiceRequirement::where('is_published', true)->where('slug', $slug)->firstOrFail();

        $related = ServiceRequirement::where('is_published', true)
            ->where('category', $item->category)
            ->whereKeyNot($item->id)
            ->orderBy('title')
            ->limit(6)
            ->get(['title', 'slug']);

        return view('service_requirement.show', compact('item', 'related'));
    }
}
