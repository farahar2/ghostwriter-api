<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CampaignBlueprintController;
use App\Http\Controllers\Api\RawContentController;

// Routes publiques — sans authentification
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});

// Routes protégées — avec authentification Sanctum
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Campaign Blueprints
    Route::apiResource('blueprints', CampaignBlueprintController::class);

    // Raw Content
    Route::post('/content/repurpose', [RawContentController::class, 'store']);
    Route::get('/content',            [RawContentController::class, 'index']);
    Route::get('/content/{rawContent}', [RawContentController::class, 'show']);

});