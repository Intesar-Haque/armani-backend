<?php

use App\Http\Controllers\Api\Client\AuthController as ClientAuthController;
use App\Http\Controllers\Api\Client\PortalController as ClientPortalController;
use App\Http\Controllers\Api\ContactQueryController;
use App\Http\Controllers\Api\EnquiryController;
use App\Http\Controllers\Api\JobApplicationController;
use App\Http\Controllers\Api\JobController;
use App\Http\Controllers\Api\LandownerController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\SiteVisitController;
use App\Http\Controllers\Api\TrackController;
use Illuminate\Support\Facades\Route;

Route::post('/enquiries', [EnquiryController::class, 'store'])
    ->middleware('throttle:20,1');

Route::post('/landowners', [LandownerController::class, 'store'])
    ->middleware('throttle:20,1');

Route::post('/site-visits', [SiteVisitController::class, 'store'])
    ->middleware('throttle:20,1');

Route::post('/submit-query', [ContactQueryController::class, 'store'])
    ->middleware('throttle:20,1');

// Browser-called relay for the optional click events (phone / WhatsApp / brochure).
Route::post('/track', [TrackController::class, 'store'])
    ->middleware('throttle:60,1');

Route::get('/jobs', [JobController::class, 'index']);
Route::get('/jobs/{jobPosting:slug}', [JobController::class, 'show']);
Route::post('/jobs/apply', [JobApplicationController::class, 'store'])
    ->middleware('throttle:20,1');
Route::post('/jobs/{jobPosting:slug}/apply', [JobApplicationController::class, 'store'])
    ->middleware('throttle:20,1');

Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{post:slug}', [PostController::class, 'show']);

Route::prefix('client')->name('api.client.')->group(function () {
    Route::post('login', [ClientAuthController::class, 'login'])->middleware('throttle:10,1')->name('login');

    Route::middleware(['auth:sanctum', 'client'])->group(function () {
        Route::post('logout', [ClientAuthController::class, 'logout'])->name('logout');
        Route::get('me', [ClientAuthController::class, 'me'])->name('me');
        Route::get('summary', [ClientPortalController::class, 'summary'])->name('summary');
        Route::get('bookings', [ClientPortalController::class, 'bookings'])->name('bookings.index');
        Route::get('bookings/{booking}', [ClientPortalController::class, 'booking'])->name('bookings.show');
        Route::get('bookings/{booking}/documents/{document}/download', [ClientPortalController::class, 'downloadDocument'])
            ->name('bookings.documents.download');
        Route::get('installments', [ClientPortalController::class, 'installments'])->name('installments.index');
        Route::get('shares', [ClientPortalController::class, 'shares'])->name('shares.index');
        Route::get('directorships', [ClientPortalController::class, 'directorships'])->name('directorships.index');
    });
});
