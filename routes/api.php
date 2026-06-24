<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

//Sans authentification
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthrController::class, 'login']);

//Avec authentification
Route::middleware('auth:sanctum')->group(function () {
Route::post('/auth.logout', [AuthController::class, 'logout']);
Route::get('/user', function (Request $request) {
    return $request->user();
});
});
