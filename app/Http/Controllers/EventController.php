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
        $events = \App\Models\Event::orderBy('start_date', 'asc')->where('start_date', '>=', now())->paginate(10);
        return view('event.index', compact('events'));
    }
}
