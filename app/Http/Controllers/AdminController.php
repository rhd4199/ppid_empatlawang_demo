<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\News;
use App\Models\Document;
use App\Models\InformationRequest;
use App\Models\Complaint;
use App\Models\Contact;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $stats = [
            'news' => News::where('is_published', true)->count(),
            'news_today' => News::where('is_published', true)->whereDate('published_at', today())->count(),
            
            'documents' => Document::count(),
            'documents_month' => Document::whereMonth('created_at', now()->month)->count(),
            
            'requests' => InformationRequest::count(),
            'requests_pending' => InformationRequest::where('status', 'pending')->count(),
            
            'complaints' => Complaint::count(),
            'complaints_pending' => Complaint::where('status', 'pending')->count(),
            
            'messages' => Contact::count(),
            'messages_unread' => Contact::where('is_read', false)->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
