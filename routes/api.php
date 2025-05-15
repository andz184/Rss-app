<?php

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\FeedController;
use Illuminate\Http\Request;
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

// Guest routes
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/register', [AuthController::class, 'register']);

// Protected routes
Route::middleware('auth:api')->group(function () {
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('me', [AuthController::class, 'me']);
    });

    // Categories routes
    Route::apiResource('categories', CategoryController::class);
    Route::post('categories/order', [CategoryController::class, 'updateOrder']);

    // Feeds routes
    Route::apiResource('feeds', FeedController::class);
    Route::post('feeds/{feed}/refresh', [FeedController::class, 'refresh']);
    Route::post('feeds/{feed}/mark-all-read', [FeedController::class, 'markAllRead']);

    // Articles routes
    Route::get('articles', [ArticleController::class, 'index']);
    Route::get('articles/{article}', [ArticleController::class, 'show']);
    Route::post('articles/{article}/toggle-read', [ArticleController::class, 'toggleRead']);
    Route::post('articles/{article}/toggle-favorite', [ArticleController::class, 'toggleFavorite']);
    Route::post('articles/mark-all-read', [ArticleController::class, 'markAllRead']);
});
