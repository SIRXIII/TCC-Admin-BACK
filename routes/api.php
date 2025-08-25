<?php

use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\PartnerController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\RiderController;
use App\Http\Controllers\Api\TravelerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:6,1');

// Route::middleware('auth:sanctum')->group(function () {

    Route::post('logout', [LoginController::class, 'logout']);


    // Dashboard
    Route::prefix('dashboard')->group(function () {
        Route::get('states', [DashboardController::class, 'getStates']);
        Route::get('travelers', [DashboardController::class, 'travelersOverview']);
        Route::get('topPartners', [DashboardController::class, 'topPartners']);
    });
    // Travelers
    Route::get('/travelers', [TravelerController::class, 'index']);
    Route::get('/travelers/{id}', [TravelerController::class, 'show']);
    Route::post('/travelers/bulk-update', [TravelerController::class, 'bulkUpdate']);
    Route::post('/travelers/export', [TravelerController::class, 'export']);



    //Partners
    Route::get('/partners', [PartnerController::class, 'index']);
    Route::get('/partners/{id}', [PartnerController::class, 'show']);

    Route::apiResource('riders', RiderController::class);
    Route::get('riders/{rider}/ratings', [RatingController::class, 'index']);



// });
