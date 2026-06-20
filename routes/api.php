<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AiChatController;
use App\Http\Controllers\Api\CampusSettingController;
use App\Http\Controllers\Api\PmbLandingContentController;
use App\Http\Controllers\Api\PmbLocalApplicationController;
use Illuminate\Support\Facades\Route;

Route::get('/settings', [CampusSettingController::class, 'show']);
Route::get('/landing-content', [PmbLandingContentController::class, 'index']);
Route::post('/ai/chat', [AiChatController::class, 'store']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/me', [AuthController::class, 'me']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::get('/registration/options', [PmbLocalApplicationController::class, 'options']);
Route::get('/registration', [PmbLocalApplicationController::class, 'show']);
Route::post('/registration', [PmbLocalApplicationController::class, 'store']);
Route::post('/registration/submit', [PmbLocalApplicationController::class, 'submit']);
Route::post('/registration/documents', [PmbLocalApplicationController::class, 'uploadDocument']);
