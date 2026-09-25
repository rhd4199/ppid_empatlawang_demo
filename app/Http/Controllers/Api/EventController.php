<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'start' => 'nullable|date',
            'end' => 'nullable|date',
        ]);

        return Event::query()
            // same "whole day" window as the admin calendar
            ->when($request->start, fn ($q, $s) => $q->where('start_date', '>=', substr($s, 0, 10).' 00:00:00'))
            ->when($request->end, fn ($q, $e) => $q->where('start_date', '<=', substr($e, 0, 10).' 23:59:59'))
            ->orderBy('start_date')
            ->get();
    }

    public function show(Event $event)
    {
        return $event;
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = Str::slug($data['title'].'-'.Str::random(5));

        return response()->json(Event::create($data), 201);
    }

    public function update(Request $request, Event $event)
    {
        $data = $this->validated($request, true);
        if (isset($data['title'])) {
            $data['slug'] = Str::slug($data['title'].'-'.Str::random(5));
        }

        $event->update($data);

        return $event;
    }

    public function destroy(Event $event)
    {
        $event->delete();

        return response()->noContent();
    }

    private function validated(Request $request, bool $partial = false): array
    {
        $req = $partial ? 'sometimes|required' : 'required';

        $data = $request->validate([
            'title' => "$req|string|max:255",
            'description' => "$req|string",
            'location' => "$req|string|max:255",
            'start_date' => "$req|date",
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        foreach (['start_date', 'end_date'] as $k) {
            if (! empty($data[$k])) {
                $data[$k] = date('Y-m-d H:i:s', strtotime($data[$k]));
            }
        }
        // admin form default: no end = end of start day
        if (! $partial && empty($data['end_date'])) {
            $data['end_date'] = substr($data['start_date'], 0, 10).' 23:59:59';
        }

        return $data;
    }
}
