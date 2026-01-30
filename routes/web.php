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
Route::get('/admin', [App\Http\Controllers\AdminController::class, 'index'])->name('admin.dashboard');
