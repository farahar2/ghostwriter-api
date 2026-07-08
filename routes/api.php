<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CampaignBlueprintController;
use App\Http\Controllers\Api\GeneratedPostController;
use App\Http\Controllers\Api\GhostwriterController;
use App\Http\Controllers\Api\RawContentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Routes publiques
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});

// Routes protégées
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Campaign Blueprints
    Route::apiResource('blueprints', CampaignBlueprintController::class);

    // Raw Content
    Route::post('/content/repurpose', [RawContentController::class, 'store']);
    Route::get('/content',            [RawContentController::class, 'index']);
    Route::get('/content/{rawContent}', [RawContentController::class, 'show']);

    // Generated Posts
    Route::get('/posts',                     [GeneratedPostController::class, 'index']);
    Route::get('/posts/{generatedPost}',     [GeneratedPostController::class, 'show']);
    Route::patch('/posts/{generatedPost}/status', [GeneratedPostController::class, 'updateStatus']);

    // Ghostwriter Agent – Chat avec mémoire
    Route::post('/posts/{generatedPost}/chat',    [GhostwriterController::class, 'chat']);
    Route::get('/posts/{generatedPost}/chat',     [GhostwriterController::class, 'history']);

});
