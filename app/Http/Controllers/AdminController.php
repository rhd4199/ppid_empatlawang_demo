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

        // Informasi Publik bento: per-category counts + latest uploads
        $infoCategories = [
            'informasi-publik-berkala' => ['label' => 'Berkala', 'icon' => 'fa-calendar-check', 'color' => 'primary'],
            'informasi-publik-serta-merta' => ['label' => 'Serta Merta', 'icon' => 'fa-bolt', 'color' => 'danger'],
            'informasi-publik-setiap-saat' => ['label' => 'Setiap Saat', 'icon' => 'fa-clock', 'color' => 'success'],
            'informasi-publik-dikecualikan' => ['label' => 'Dikecualikan', 'icon' => 'fa-lock', 'color' => 'secondary'],
        ];
        $infoCounts = Document::whereIn('category', array_keys($infoCategories))
            ->selectRaw('category, count(*) as total, sum(case when is_published then 1 else 0 end) as published')
            ->groupBy('category')
            ->get()
            ->keyBy('category');
        $infoLatest = Document::whereIn('category', array_keys($infoCategories))->latest()->take(6)->get();

        return view('admin.dashboard', compact('stats', 'infoCategories', 'infoCounts', 'infoLatest'));
    }
}
