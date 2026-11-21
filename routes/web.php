<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InfoPublicController;
use App\Http\Controllers\StandardServiceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ProcurementController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\InformationRequestController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\SitemapController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap');
// served here (not public/robots.txt) so the Sitemap line uses this host's URL
Route::get('/robots.txt', [SitemapController::class, 'robots']);

Route::get('/profil', [ProfileController::class, 'index'])->name('profiles.index');
Route::get('/profil-pejabat', [App\Http\Controllers\OfficialController::class, 'index'])->name('officials.index');
Route::get('/profil/{slug}', [ProfileController::class, 'show'])->name('profiles.show');

Route::resource('informasi-publik', InfoPublicController::class)->only(['index', 'show'])
    ->where(['informasi_publik' => '[0-9]+']);
Route::get('/informasi-publik/{id}/unduh', [InfoPublicController::class, 'download'])->whereNumber('id')->name('informasi-publik.download');
Route::resource('standar-layanan', StandardServiceController::class)->only(['index'])->names('standard-service');
Route::get('/persyaratan', [App\Http\Controllers\ServiceRequirementController::class, 'index'])->name('service-requirements.index');
Route::get('/persyaratan/{slug}', [App\Http\Controllers\ServiceRequirementController::class, 'show'])->name('service-requirements.show');
Route::resource('laporan', ReportController::class)->only(['index'])->names('reports');
Route::resource('berita', NewsController::class)
    ->only(['index', 'show'])
    ->parameters(['berita' => 'news:slug'])
    ->names('news');
Route::resource('galeri', GalleryController::class)->only(['index', 'show'])->names('galleries');
Route::resource('event', EventController::class)->only(['index'])->names('events');
Route::resource('pengadaan', ProcurementController::class)->only(['index'])->names('procurements');

Route::get('/kontak', [ContactController::class, 'index'])->name('contact.index');
Route::post('/kontak', [ContactController::class, 'store'])->name('contact.store');

Route::get('/permohonan-informasi', [InformationRequestController::class, 'create'])->name('request.create');
Route::post('/permohonan-informasi', [InformationRequestController::class, 'store'])->name('request.store');
Route::get('/cek-status-permohonan', [InformationRequestController::class, 'checkStatus'])->name('request.status');

Route::get('/cuaca', [App\Http\Controllers\WeatherController::class, 'show'])->middleware('throttle:60,1')->name('weather.show');
Route::get('/bencana', [App\Http\Controllers\DisasterController::class, 'show'])->middleware('throttle:60,1')->name('disaster.show');
Route::post('/sapa-madani/chat', [App\Http\Controllers\AssistantController::class, 'chat'])->middleware('throttle:20,1')->name('assistant.chat');

Route::get('/pengajuan-keberatan', [ComplaintController::class, 'create'])->name('complaint.create');
Route::post('/pengajuan-keberatan', [ComplaintController::class, 'store'])->name('complaint.store');

// No public sign-up: every account is an admin, so /register let anyone into /admin.
Auth::routes(['register' => false]);

// /home was a second copy of the homepage; keep old links working without duplicate content.
Route::permanentRedirect('/home', '/');
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [App\Http\Controllers\AdminController::class, 'index'])->name('dashboard');

    // Summernote image upload (keeps base64 out of TEXT columns); "public" disk = S3
    Route::post('/editor/upload', function (\Illuminate\Http\Request $request) {
        $request->validate(['file' => 'required|image|max:5120']);

        return response()->json([
            'url' => storage_url(\Illuminate\Support\Facades\Storage::disk('public')
                ->putFile('editor', $request->file('file'), 'public')),
        ]);
    })->name('editor.upload');
    
    // Profiles
    Route::get('/profil/{slug}/edit', [App\Http\Controllers\Admin\ProfileController::class, 'edit'])->name('profiles.edit');
    Route::put('/profil/{id}', [App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profiles.update');

    // Pejabat / Pejabat Struktural
    Route::post('/pejabat/{id}/toggle-status', [App\Http\Controllers\Admin\OfficialController::class, 'toggleStatus'])->name('officials.toggle-status');
    Route::resource('pejabat', App\Http\Controllers\Admin\OfficialController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->names('officials');

    // Documents Management
    Route::post('/informasi-publik/bulk-action', [App\Http\Controllers\Admin\InfoPublicController::class, 'bulkAction'])->name('info-public.bulk-action');
    Route::post('/informasi-publik/{id}/toggle-status', [App\Http\Controllers\Admin\InfoPublicController::class, 'toggleStatus'])->name('info-public.toggle-status');
    Route::resource('informasi-publik', App\Http\Controllers\Admin\InfoPublicController::class)->names('info-public');
    
    Route::post('/standar-layanan/bulk-action', [App\Http\Controllers\Admin\StandardServiceController::class, 'bulkAction'])->name('standard-service.bulk-action');
    Route::post('/standar-layanan/{id}/toggle-status', [App\Http\Controllers\Admin\StandardServiceController::class, 'toggleStatus'])->name('standard-service.toggle-status');
    Route::resource('standar-layanan', App\Http\Controllers\Admin\StandardServiceController::class)->names('standard-service');

    // Persyaratan layanan instansi lain (Disdukcapil, Samsat, BPN, ...)
    Route::post('/persyaratan-layanan/bulk-action', [App\Http\Controllers\Admin\ServiceRequirementController::class, 'bulkAction'])->name('service-requirements.bulk-action');
    Route::post('/persyaratan-layanan/{id}/toggle-status', [App\Http\Controllers\Admin\ServiceRequirementController::class, 'toggleStatus'])->name('service-requirements.toggle-status');
    Route::resource('persyaratan-layanan', App\Http\Controllers\Admin\ServiceRequirementController::class)
        ->except(['show'])
        ->parameters(['persyaratan-layanan' => 'id'])
        ->names('service-requirements');

    Route::post('/laporan/bulk-action', [App\Http\Controllers\Admin\ReportController::class, 'bulkAction'])->name('reports.bulk-action');
    Route::post('/laporan/{id}/toggle-status', [App\Http\Controllers\Admin\ReportController::class, 'toggleStatus'])->name('reports.toggle-status');
    Route::resource('laporan', App\Http\Controllers\Admin\ReportController::class)->names('reports');
    Route::post('/pengadaan/bulk-action', [App\Http\Controllers\Admin\ProcurementController::class, 'bulkAction'])->name('procurements.bulk-action');
    Route::resource('pengadaan', App\Http\Controllers\Admin\ProcurementController::class)->names('procurements');

    // Info & Berita
    Route::post('/berita/bulk-action', [App\Http\Controllers\Admin\NewsController::class, 'bulkAction'])->name('news.bulk-action');
    Route::post('/berita/{id}/toggle-status', [App\Http\Controllers\Admin\NewsController::class, 'toggleStatus'])->name('news.toggle-status');
    Route::post('/berita/{id}/toggle-headline', [App\Http\Controllers\Admin\NewsController::class, 'toggleHeadline'])->name('news.toggle-headline');
    Route::resource('berita', App\Http\Controllers\Admin\NewsController::class)->names('news');
    Route::post('/galeri/{id}/upload-photos', [App\Http\Controllers\Admin\GalleryController::class, 'uploadPhotos'])->name('galleries.upload-photos');
    Route::post('/galeri/{id}/upload-chunk', [App\Http\Controllers\Admin\GalleryController::class, 'uploadChunk'])->name('galleries.upload-chunk');
    Route::delete('/galeri/photo/{id}', [App\Http\Controllers\Admin\GalleryController::class, 'deletePhoto'])->name('galleries.delete-photo');
    Route::post('/galeri/{id}/update-order', [App\Http\Controllers\Admin\GalleryController::class, 'updatePhotoOrder'])->name('galleries.update-order');
    Route::post('/galeri/bulk-action', [App\Http\Controllers\Admin\GalleryController::class, 'bulkAction'])->name('galleries.bulk-action');
    Route::post('/galeri/{id}/toggle-status', [App\Http\Controllers\Admin\GalleryController::class, 'toggleStatus'])->name('galleries.toggle-status');
    Route::resource('galeri', App\Http\Controllers\Admin\GalleryController::class)->names('galleries');
    Route::resource('agenda', App\Http\Controllers\Admin\EventController::class)->names('events');

    // Contact / Inbox
    Route::get('/pesan-masuk', [App\Http\Controllers\Admin\ContactController::class, 'index'])->name('contact.index');
    Route::delete('/pesan-masuk/{id}', [App\Http\Controllers\Admin\ContactController::class, 'destroy'])->name('contact.destroy');
    Route::post('/pesan-masuk/{id}/read', [App\Http\Controllers\Admin\ContactController::class, 'markAsRead'])->name('contact.read');

    // Contact Settings
    Route::get('/pengaturan-kontak', [App\Http\Controllers\Admin\ContactSettingController::class, 'index'])->name('contact-settings.index');
    Route::put('/pengaturan-kontak', [App\Http\Controllers\Admin\ContactSettingController::class, 'update'])->name('contact-settings.update');
    Route::put('/pengaturan-kontak/asisten', [App\Http\Controllers\Admin\ContactSettingController::class, 'updateAssistant'])->name('contact-settings.update-assistant');
    Route::put('/pengaturan-kontak/statistik', [App\Http\Controllers\Admin\ContactSettingController::class, 'updateStats'])->name('contact-settings.update-stats');

    // WhatsApp inbox (messages from the provider webhook)
    Route::get('/kotak-masuk-wa', [App\Http\Controllers\Admin\WhatsAppInboxController::class, 'index'])->name('wa-inbox.index');
    Route::get('/kotak-masuk-wa/ringkasan', [App\Http\Controllers\Admin\WhatsAppInboxController::class, 'summary'])->name('wa-inbox.summary');
    Route::get('/kotak-masuk-wa/{conversation}/pesan', [App\Http\Controllers\Admin\WhatsAppInboxController::class, 'messages'])->name('wa-inbox.messages');
    Route::get('/kotak-masuk-wa/{conversation}', [App\Http\Controllers\Admin\WhatsAppInboxController::class, 'show'])->name('wa-inbox.show');
    Route::post('/kotak-masuk-wa/{conversation}/draf', [App\Http\Controllers\Admin\WhatsAppInboxController::class, 'redraft'])->name('wa-inbox.redraft');
    Route::post('/kotak-masuk-wa/{conversation}/kirim', [App\Http\Controllers\Admin\WhatsAppInboxController::class, 'send'])->name('wa-inbox.send');

    // WhatsApp Gateway
    Route::get('/whatsapp-gateway', [App\Http\Controllers\Admin\WhatsAppGatewayController::class, 'index'])->name('whatsapp.index');
    Route::put('/whatsapp-gateway', [App\Http\Controllers\Admin\WhatsAppGatewayController::class, 'update'])->name('whatsapp.update');
    Route::post('/whatsapp-gateway/uji', [App\Http\Controllers\Admin\WhatsAppGatewayController::class, 'test'])->name('whatsapp.test');

    // Account Settings
    Route::get('/akun', [App\Http\Controllers\Admin\AccountController::class, 'index'])->name('account.index');
    Route::put('/akun', [App\Http\Controllers\Admin\AccountController::class, 'update'])->name('account.update');

    // Requests & Complaints
    Route::resource('permohonan-informasi', App\Http\Controllers\Admin\InformationRequestController::class)
        ->only(['index', 'show', 'update', 'destroy'])
        ->names('requests');
        
    Route::resource('pengajuan-keberatan', App\Http\Controllers\Admin\ComplaintController::class)
        ->only(['index', 'show', 'update', 'destroy'])
        ->names('complaints');
});
