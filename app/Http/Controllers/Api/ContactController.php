<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['per_page' => 'nullable|integer|min:1|max:100']);

        return Contact::query()
            ->when($request->has('is_read'), fn ($q) => $q->where('is_read', $request->boolean('is_read')))
            ->latest()
            ->paginate($request->integer('per_page', 15));
    }

    public function show(Contact $contact)
    {
        return $contact;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        return response()->json(Contact::create($data), 201);
    }

    public function markAsRead(Contact $contact)
    {
        $contact->update(['is_read' => true]);

        return $contact;
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();

        return response()->noContent();
    }
}
