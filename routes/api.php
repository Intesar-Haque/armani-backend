<?php

use App\Http\Controllers\Api\ContactQueryController;
use App\Http\Controllers\Api\EnquiryController;
use App\Http\Controllers\Api\JobApplicationController;
use App\Http\Controllers\Api\JobController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\SiteVisitController;
use Illuminate\Support\Facades\Route;

Route::post('/enquiries', [EnquiryController::class, 'store'])
    ->middleware('throttle:20,1');

Route::post('/site-visits', [SiteVisitController::class, 'store'])
    ->middleware('throttle:20,1');

Route::post('/submit-query', [ContactQueryController::class, 'store'])
    ->middleware('throttle:20,1');

Route::get('/jobs', [JobController::class, 'index']);
Route::get('/jobs/{jobPosting:slug}', [JobController::class, 'show']);
Route::post('/jobs/apply', [JobApplicationController::class, 'store'])
    ->middleware('throttle:20,1');
Route::post('/jobs/{jobPosting:slug}/apply', [JobApplicationController::class, 'store'])
    ->middleware('throttle:20,1');

Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{post:slug}', [PostController::class, 'show']);
