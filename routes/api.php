<?php

use App\Http\Controllers\Api;
use App\Http\Middleware\BearerToken;
use Illuminate\Support\Facades\Route;

// All endpoints: Authorization: Bearer <API_TOKEN>. Docs: API.md
Route::prefix('v1')->name('api.')->middleware([BearerToken::class, 'throttle:120,1'])->group(function () {
    Route::post('documents/bulk', [Api\DocumentController::class, 'bulk']);
    Route::post('documents/{document}/toggle-status', [Api\DocumentController::class, 'toggleStatus']);
    Route::apiResource('documents', Api\DocumentController::class);

    Route::post('news/bulk', [Api\NewsController::class, 'bulk']);
    Route::post('news/{news}/toggle-status', [Api\NewsController::class, 'toggleStatus']);
    Route::post('news/{news}/toggle-headline', [Api\NewsController::class, 'toggleHeadline']);
    Route::apiResource('news', Api\NewsController::class)->parameters(['news' => 'news']);

    Route::post('galleries/bulk', [Api\GalleryController::class, 'bulk']);
    Route::post('galleries/{gallery}/toggle-status', [Api\GalleryController::class, 'toggleStatus']);
    Route::post('galleries/{gallery}/photos', [Api\GalleryController::class, 'uploadPhotos']);
    Route::put('galleries/{gallery}/photos/order', [Api\GalleryController::class, 'updateOrder']);
    Route::delete('gallery-photos/{item}', [Api\GalleryController::class, 'deletePhoto']);
    Route::apiResource('galleries', Api\GalleryController::class);

    Route::apiResource('events', Api\EventController::class);

    Route::post('officials/{official}/toggle-status', [Api\OfficialController::class, 'toggleStatus']);
    Route::apiResource('officials', Api\OfficialController::class);

    Route::get('profiles', [Api\ProfileController::class, 'index']);
    Route::get('profiles/{slug}', [Api\ProfileController::class, 'show']);
    Route::match(['put', 'patch'], 'profiles/{slug}', [Api\ProfileController::class, 'update']);

    Route::post('contacts/{contact}/read', [Api\ContactController::class, 'markAsRead']);
    Route::apiResource('contacts', Api\ContactController::class)->except('update');

    Route::apiResource('information-requests', Api\InformationRequestController::class);
    Route::apiResource('complaints', Api\ComplaintController::class);

    Route::get('settings/contact', [Api\SettingController::class, 'showContact']);
    Route::match(['put', 'patch'], 'settings/contact', [Api\SettingController::class, 'updateContact']);
    Route::get('settings/stats', [Api\SettingController::class, 'showStats']);
    Route::match(['put', 'patch'], 'settings/stats', [Api\SettingController::class, 'updateStats']);
});
