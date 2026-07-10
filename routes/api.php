<?php

use App\Http\Controllers\Api\ContactQueryController;
use App\Http\Controllers\Api\EnquiryController;
use App\Http\Controllers\Api\SiteVisitController;
use Illuminate\Support\Facades\Route;

Route::post('/enquiries', [EnquiryController::class, 'store'])
    ->middleware('throttle:20,1');

Route::post('/site-visits', [SiteVisitController::class, 'store'])
    ->middleware('throttle:20,1');

Route::post('/submit-query', [ContactQueryController::class, 'store'])
    ->middleware('throttle:20,1');
