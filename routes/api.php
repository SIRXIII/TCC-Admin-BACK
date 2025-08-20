<?php

use App\Http\Controllers\Api\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:6,1');

Route::middleware('auth:sanctum')->group(function () {

    Route::post('logout', [LoginController::class, 'logout']);

});
