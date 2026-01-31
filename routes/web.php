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

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/profil', [ProfileController::class, 'index'])->name('profiles.index');
Route::get('/profil/{slug}', [ProfileController::class, 'show'])->name('profiles.show');

Route::resource('informasi-publik', InfoPublicController::class);
Route::resource('standar-layanan', StandardServiceController::class)->names('standard-service');
Route::resource('laporan', ReportController::class)->names('reports');
Route::resource('berita', NewsController::class)
    ->parameters(['berita' => 'news:slug'])
    ->names('news');
Route::resource('galeri', GalleryController::class)->names('galleries');
Route::resource('event', EventController::class)->names('events');
Route::resource('pengadaan', ProcurementController::class)->names('procurements');

Route::get('/kontak', [ContactController::class, 'index'])->name('contact.index');
Route::post('/kontak', [ContactController::class, 'store'])->name('contact.store');

Route::get('/permohonan-informasi', [InformationRequestController::class, 'create'])->name('request.create');
Route::post('/permohonan-informasi', [InformationRequestController::class, 'store'])->name('request.store');
Route::get('/cek-status-permohonan', [InformationRequestController::class, 'checkStatus'])->name('request.status');

Route::get('/pengajuan-keberatan', [ComplaintController::class, 'create'])->name('complaint.create');
Route::post('/pengajuan-keberatan', [ComplaintController::class, 'store'])->name('complaint.store');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [App\Http\Controllers\AdminController::class, 'index'])->name('dashboard');
    
    // Profiles
    Route::get('/profil/{slug}/edit', [App\Http\Controllers\Admin\ProfileController::class, 'edit'])->name('profiles.edit');
    Route::put('/profil/{id}', [App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profiles.update');

    // Documents Management
    Route::post('/informasi-publik/bulk-action', [App\Http\Controllers\Admin\InfoPublicController::class, 'bulkAction'])->name('info-public.bulk-action');
    Route::post('/informasi-publik/{id}/toggle-status', [App\Http\Controllers\Admin\InfoPublicController::class, 'toggleStatus'])->name('info-public.toggle-status');
    Route::resource('informasi-publik', App\Http\Controllers\Admin\InfoPublicController::class)->names('info-public');
    
    Route::post('/standar-layanan/bulk-action', [App\Http\Controllers\Admin\StandardServiceController::class, 'bulkAction'])->name('standard-service.bulk-action');
    Route::post('/standar-layanan/{id}/toggle-status', [App\Http\Controllers\Admin\StandardServiceController::class, 'toggleStatus'])->name('standard-service.toggle-status');
    Route::resource('standar-layanan', App\Http\Controllers\Admin\StandardServiceController::class)->names('standard-service');

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
