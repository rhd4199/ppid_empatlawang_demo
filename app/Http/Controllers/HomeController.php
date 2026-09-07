<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\News;
use App\Models\Gallery;
use App\Models\Document;
use App\Models\ContactSetting;
use App\Models\PpidSetting;
use App\Models\InformationRequest;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $news = News::where('is_published', true)
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->take(3)
            ->get();
        $galleries = Gallery::latest()->take(9)->get(); // Fetch latest 9 for carousel (3 slides x 3 items)

        // Informasi Serta Merta (peringatan dini/evakuasi) highlighted as home sections
        $emergencyInfo = Document::where('category', 'informasi-publik-serta-merta')
            ->where('is_published', true)
            ->orderBy('id')
            ->get();

        $contactSettings = ContactSetting::first();

        // Homepage stats — Informasi Publik & Permohonan Selesai computed live from data,
        // Indeks Kepuasan is admin-editable (no survey data source exists yet).
        $stats = [
            'informasi_publik' => Document::whereIn('category', [
                'informasi-publik-berkala',
                'informasi-publik-serta-merta',
                'informasi-publik-setiap-saat',
                'informasi-publik-dikecualikan',
            ])->where('is_published', true)->count(),
            'permohonan_selesai' => InformationRequest::whereIn('status', ['approved', 'rejected'])->count(),
            'satisfaction_index' => PpidSetting::where('key', 'stat_satisfaction_index')->value('value') ?? '98%',
        ];

        return view('home', compact('news', 'galleries', 'emergencyInfo', 'contactSettings', 'stats'));
    }
}
