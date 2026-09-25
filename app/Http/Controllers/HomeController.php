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

        // Informasi Publik bento: published count per category + latest uploads
        $infoCategories = [
            'informasi-publik-berkala' => ['slug' => 'berkala', 'label' => 'Informasi Berkala', 'desc' => 'Disediakan dan diumumkan secara rutin', 'icon' => 'fa-calendar-check', 'color' => 'primary'],
            'informasi-publik-serta-merta' => ['slug' => 'serta-merta', 'label' => 'Informasi Serta Merta', 'desc' => 'Menyangkut hajat hidup orang banyak', 'icon' => 'fa-bolt', 'color' => 'danger'],
            'informasi-publik-setiap-saat' => ['slug' => 'setiap-saat', 'label' => 'Informasi Setiap Saat', 'desc' => 'Tersedia kapan pun diminta', 'icon' => 'fa-clock', 'color' => 'success'],
            'informasi-publik-dikecualikan' => ['slug' => 'dikecualikan', 'label' => 'Informasi Dikecualikan', 'desc' => 'Tidak dapat diakses publik', 'icon' => 'fa-lock', 'color' => 'secondary'],
        ];
        $infoCounts = Document::whereIn('category', array_keys($infoCategories))
            ->where('is_published', true)
            ->selectRaw('category, count(*) as total')
            ->groupBy('category')
            ->pluck('total', 'category');
        $infoLatest = Document::whereIn('category', array_keys($infoCategories))
            ->where('is_published', true)
            ->latest()
            ->take(5)
            ->get();

        // Homepage stats — Informasi Publik & Permohonan Selesai computed live from data,
        // Indeks Kepuasan is admin-editable (no survey data source exists yet).
        $stats = [
            'informasi_publik' => $infoCounts->sum(),
            'permohonan_selesai' => InformationRequest::whereIn('status', ['approved', 'rejected'])->count(),
            'satisfaction_index' => PpidSetting::where('key', 'stat_satisfaction_index')->value('value') ?? '98%',
        ];

        return view('home', compact('news', 'galleries', 'emergencyInfo', 'contactSettings', 'stats', 'infoCategories', 'infoCounts', 'infoLatest'));
    }
}
