<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CampaignBlueprintController;

// Routes publiques — sans authentification
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});

// Routes protégées — avec authentification Sanctum
Route::middleware('auth:sanctum')->group(function () {
Route::post('/auth/logout', [AuthController::class, 'logout']);
Route::apiResource('blueprints', CampaignBlueprintController::class)
    ->parameters(['blueprints' => 'campaignBlueprint']);
});