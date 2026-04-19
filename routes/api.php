<?php

use App\Http\Controllers\Api\Auth\AccessTokenController;
use App\Http\Controllers\Api\CandidateController;
use App\Http\Controllers\Api\SemanticSearchController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/token', [AccessTokenController::class, 'store'])
    ->middleware('throttle:10,1');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::delete('/auth/token', [AccessTokenController::class, 'destroy']);
    Route::get('/user', UserController::class);
    Route::get('/candidates', [CandidateController::class, 'index']);
    Route::post('/search', [SemanticSearchController::class, 'store'])
        ->middleware('throttle:20,1');
});
