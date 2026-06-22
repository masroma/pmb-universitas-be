<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AiChatController;
use App\Http\Controllers\Api\AiChatMemoryController;
use App\Http\Controllers\Api\AiPmbDataController;
use App\Http\Controllers\Api\CampusSettingController;
use App\Http\Controllers\Api\PmbInformationSectionController;
use App\Http\Controllers\Api\PmbLandingContentController;
use App\Http\Controllers\Api\PmbLocalApplicationController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    DB::select('select 1');

    return response()->json([
        'status' => 'ok',
        'service' => 'pmb-backend',
        'timestamp' => now()->toISOString(),
    ]);
});

Route::get('/settings', [CampusSettingController::class, 'show']);
Route::get('/landing-content', [PmbLandingContentController::class, 'index']);
Route::get('/pmb-information', [PmbInformationSectionController::class, 'index']);
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


Route::prefix('ai')->group(function () {
    Route::get('/biaya', [AiPmbDataController::class, 'tuitionFees']);
    Route::get('/jalur-pendaftaran', [AiPmbDataController::class, 'registrationPaths']);
    Route::get('/program-studi', [AiPmbDataController::class, 'studyPrograms']);
    Route::get('/beasiswa', [AiPmbDataController::class, 'scholarships']);
    Route::get('/konten-pmb', [AiPmbDataController::class, 'pmbContent']);
    Route::get('/kelas', [AiPmbDataController::class, 'classes']);
    Route::get('/data-kampus', [AiPmbDataController::class, 'campusData']);
    Route::get('/alur-pendaftaran', [AiPmbDataController::class, 'registrationFlows']);
    Route::get('/periode-pendaftaran', [AiPmbDataController::class, 'registrationPeriods']);
    Route::get('/jadwal-pendaftaran', [AiPmbDataController::class, 'registrationPeriods']);
    Route::get('/syarat-pendaftaran', [AiPmbDataController::class, 'admissionRequirements']);
    Route::get('/kurikulum', [AiPmbDataController::class, 'curriculum']);
    Route::get('/kontak-pmb', [AiPmbDataController::class, 'pmbContacts']);
    Route::get('/brosur', [AiPmbDataController::class, 'brochure']);
    Route::get('/keunggulan-kampus', [AiPmbDataController::class, 'campusBenefits']);
    Route::get('/opsi-pendaftaran', [AiPmbDataController::class, 'registrationOptions']);
    Route::post('/memory/conversations', [AiChatMemoryController::class, 'ensureConversation']);
    Route::get('/memory/conversations/{conversationId}/messages', [AiChatMemoryController::class, 'messages']);
    Route::post('/memory/conversations/{conversationId}/messages', [AiChatMemoryController::class, 'storeMessage']);
    Route::post('/memory/conversations/{conversationId}/lead', [AiChatMemoryController::class, 'storeLead']);
});