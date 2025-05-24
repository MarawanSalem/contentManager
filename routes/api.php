<?php

use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\PlatformController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Posts
    Route::apiResource('posts', PostController::class);

    // Platforms
    Route::get('platforms', [PlatformController::class, 'index']);
    Route::get('platforms/active', [PlatformController::class, 'active']);
    Route::post('platforms/{id}/toggle', [PlatformController::class, 'toggle']);
    Route::post('platforms/{id}/validate', [PlatformController::class, 'validatePost']);

    // Auth
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('user', [AuthController::class, 'user']);
});
