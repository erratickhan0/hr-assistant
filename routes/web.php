<?php

use App\Http\Controllers\Auth\RegisteredOrganizationController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HrSemanticSearchController;
use App\Http\Controllers\Public\AgencyPortalController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::get('/register', [RegisteredOrganizationController::class, 'create'])->name('register');
Route::post('/register', [RegisteredOrganizationController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('register.store');

Route::get('/login', [SessionController::class, 'create'])->name('login');
Route::post('/login', [SessionController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('login.store');

Route::post('/logout', [SessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::post('/dashboard/search', HrSemanticSearchController::class)
        ->middleware('throttle:20,1')
        ->name('hr.search');
});

Route::get('/agency/{organization}', [AgencyPortalController::class, 'show'])->name('agency.portal');
Route::post('/agency/{organization}/cv', [AgencyPortalController::class, 'store'])
    ->middleware('throttle:8,1')
    ->name('agency.cv.store');
