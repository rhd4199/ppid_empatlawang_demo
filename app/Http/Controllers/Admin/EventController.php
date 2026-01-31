<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EventController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                $query = Event::query();
                
                if ($request->filled('start') && $request->filled('end')) {
                    // Log the incoming request for debugging
                    Log::info('Event Fetch Request', [
                        'start_raw' => $request->start,
                        'end_raw' => $request->end
                    ]);

                    // Parse dates handling timezone logic
                    // If DB stores local time (e.g. WIB) without timezone, and we want to match visual range:
                    // Option 1: Convert request (ISO8601) to UTC (standard Laravel behavior)
                    // $start = Carbon::parse($request->start);
                    // $end = Carbon::parse($request->end);

                    // Option 2: Use the "visual" date string sent by FullCalendar (ignoring timezone shift)
                    // This matches "what you see is what you query" if DB stores "what you see"
                    $start = substr($request->start, 0, 10) . ' 00:00:00';
                    $end = substr($request->end, 0, 10) . ' 23:59:59';
                    
                    Log::info('Event Query Filters', ['start' => $start, 'end' => $end]);

                    $query->whereBetween('start_date', [$start, $end]);
                }
                
                $events = $query->get();
                
                Log::info('Events Found', ['count' => $events->count()]);

                return response()->json($events->map(function ($event) {
                    return [
                        'id' => $event->id,
                        'title' => $event->title,
                        // Ensure 'T' separator for FullCalendar compatibility
                        'start' => str_replace(' ', 'T', $event->start_date),
                        'end' => $event->end_date ? str_replace(' ', 'T', $event->end_date) : null,
                        'extendedProps' => [
                            'description' => $event->description,
                            'location' => $event->location,
                        ],
                    ];
                }));
            } catch (\Exception $e) {
                Log::error('Event Fetch Error', ['error' => $e->getMessage()]);
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        $events = Event::latest('start_date')->get();
        return view('admin.event.index', compact('events'));
    }

    public function create()
    {
        return view('admin.event.create');
    }

    public function store(Request $request)
    {
        // Handle inputs from both Form (split date/time) and FullCalendar (ISO string)
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            // Allow either composite start_date OR split start_date_d
            'start_date' => 'required_without:start_date_d', 
            'start_date_d' => 'required_without:start_date',
        ]);

        $start = $this->parseDateTime($request, 'start');
        $end = $this->parseDateTime($request, 'end', $start);

        $event = Event::create([
            'title' => $request->title,
            'slug' => Str::slug($request->title . '-' . Str::random(5)),
            'description' => $request->description,
            'location' => $request->location,
            'start_date' => $start,
            'end_date' => $end,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Agenda kegiatan berhasil ditambahkan.',
                'event' => $event
            ]);
        }

        return redirect()->route('admin.events.index')->with('success', 'Agenda kegiatan berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $event = Event::findOrFail($id);
        return view('admin.event.edit', compact('event'));
    }

    public function update(Request $request, $id)
    {
        $event = Event::findOrFail($id);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
        ]);

        $start = $this->parseDateTime($request, 'start');
        // If end is not provided in update, we might want to keep existing? 
        // No, form sends what it has. If optional, we recalculate.
        $end = $this->parseDateTime($request, 'end', $start);

        $event->update([
            'title' => $request->title,
            // Only update slug if title changed? Or always? User said "update nama tidak bisa".
            // Let's keep it simple.
            'slug' => Str::slug($request->title . '-' . Str::random(5)),
            'description' => $request->description,
            'location' => $request->location,
            'start_date' => $start,
            'end_date' => $end,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Agenda kegiatan berhasil diperbarui.',
                'event' => $event
            ]);
        }

        return redirect()->route('admin.events.index')->with('success', 'Agenda kegiatan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $event = Event::findOrFail($id);
        $event->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Agenda kegiatan berhasil dihapus.'
            ]);
        }

        return redirect()->route('admin.events.index')->with('success', 'Agenda kegiatan berhasil dihapus.');
    }

    private function parseDateTime(Request $request, $prefix, $fallbackStart = null)
    {
        // 1. Check for split fields (date + time)
        if ($request->filled("{$prefix}_date_d")) {
            $date = $request->input("{$prefix}_date_d");
            // If date is empty but we are processing 'end', use start date
            if (!$date && $prefix === 'end' && $fallbackStart) {
                $date = date('Y-m-d', strtotime($fallbackStart));
            }
            
            if (!$date) return null; // No date provided

            $time = $request->input("{$prefix}_date_t");
            if (!$time) {
                // Default times if optional
                $time = ($prefix === 'start') ? '00:00:00' : '23:59:59';
            }
            return "$date $time";
        }

        // 2. Check for composite field (ISO string or datetime-local)
        if ($request->filled("{$prefix}_date")) {
            // FullCalendar might send ISO, datetime-local sends T separated
            return date('Y-m-d H:i:s', strtotime($request->input("{$prefix}_date")));
        }
        
        // 3. Fallback for End Date
        if ($prefix === 'end' && $fallbackStart) {
             // If end not specified, default to Start Date EOD
             return date('Y-m-d', strtotime($fallbackStart)) . ' 23:59:59';
        }

        return null;
    }
}
