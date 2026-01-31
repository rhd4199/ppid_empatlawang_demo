<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Data for "This Month" (Calendar View)
        $currentDate = now();
        
        // Calculate calendar range (start of week of start of month -> end of week of end of month)
        $startOfMonth = $currentDate->copy()->startOfMonth();
        $endOfMonth = $currentDate->copy()->endOfMonth();
        $startOfCalendar = $startOfMonth->copy()->startOfWeek();
        $endOfCalendar = $endOfMonth->copy()->endOfWeek();
        
        // Fetch events for the calendar grid (including padding days)
        $calendarEvents = \App\Models\Event::whereBetween('start_date', [$startOfCalendar, $endOfCalendar])
            ->orderBy('start_date')
            ->get();

        // Data for "This Year" (Timeline/List View)
        $yearEventsCollection = \App\Models\Event::whereYear('start_date', $currentDate->year)
            ->orderBy('start_date')
            ->get();
            
        $yearEvents = $yearEventsCollection->groupBy(function($event) {
                return \Carbon\Carbon::parse($event->start_date)->format('F Y');
            });

        // Merge all events for modal generation
        $allEvents = $calendarEvents->merge($yearEventsCollection)->unique('id');

        return view('event.index', compact('calendarEvents', 'yearEvents', 'allEvents', 'currentDate', 'startOfCalendar', 'endOfCalendar'));
    }
}
