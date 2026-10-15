<?php

use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\ContactQueryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EnquiryController;
use App\Http\Controllers\Admin\JobApplicationController;
use App\Http\Controllers\Admin\JobPostingController;
use App\Http\Controllers\Admin\LandownerController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\ProjectDirectorController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ShareAssignmentController;
use App\Http\Controllers\Admin\SiteVisitController;
use App\Http\Controllers\Admin\ApartmentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Client\BookingController as ClientBookingController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->isClient()
            ? redirect()->route('client.dashboard')
            : redirect()->route('admin.dashboard');
    }
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store']);
    Route::get('register', [RegisterController::class, 'create'])->name('register');
    Route::post('register', [RegisterController::class, 'store'])->middleware('throttle:10,1');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::resource('projects', ProjectController::class)->except(['show']);

        Route::get('apartments', [ApartmentController::class, 'index'])->name('apartments.index');
        Route::put('apartments/{apartment}', [ApartmentController::class, 'update'])->name('apartments.update');

        Route::resource('clients', ClientController::class)->only(['index', 'store']);

        Route::resource('bookings', BookingController::class)->except(['edit']);
        Route::post('bookings/{booking}/installments/{installment}/pay', [BookingController::class, 'payInstallment'])
            ->name('bookings.installments.pay');
        Route::post('bookings/{booking}/documents', [BookingController::class, 'uploadDocument'])
            ->name('bookings.documents.store');
        Route::get('bookings/{booking}/documents/{document}/download', [BookingController::class, 'downloadDocument'])
            ->name('bookings.documents.download');
        Route::delete('bookings/{booking}/documents/{document}', [BookingController::class, 'destroyDocument'])
            ->name('bookings.documents.destroy');

        Route::resource('enquiries', EnquiryController::class)->only(['index', 'show', 'update', 'destroy']);
        Route::resource('landowners', LandownerController::class)->only(['index', 'show', 'update', 'destroy']);
        Route::resource('site-visits', SiteVisitController::class)->only(['index', 'show', 'update', 'destroy']);
        Route::resource('queries', ContactQueryController::class)
            ->only(['index', 'show', 'update', 'destroy'])
            ->parameter('queries', 'query');

        Route::resource('jobs', JobPostingController::class);
        Route::resource('job-applications', JobApplicationController::class)->only(['index', 'show', 'update', 'destroy']);
        Route::get('job-applications/{jobApplication}/cv', [JobApplicationController::class, 'downloadCv'])
            ->name('job-applications.cv');

        Route::resource('posts', PostController::class);

        Route::resource('share-assignments', ShareAssignmentController::class)->except(['edit']);

        Route::resource('project-directors', ProjectDirectorController::class)->except(['show', 'edit']);
    });

    Route::middleware('client')->prefix('portal')->name('client.')->group(function () {
        Route::get('/', ClientDashboardController::class)->name('dashboard');
        Route::get('bookings/{booking}', [ClientBookingController::class, 'show'])->name('bookings.show');
        Route::get('bookings/{booking}/documents/{document}/download', [ClientBookingController::class, 'downloadDocument'])
            ->name('bookings.documents.download');
        Route::get('bookings/{booking}/receipts/{document}', [ClientBookingController::class, 'viewReceipt'])
            ->name('bookings.receipt');
    });
});
