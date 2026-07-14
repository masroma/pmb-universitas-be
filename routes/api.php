<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\AiChatController;
use App\Http\Controllers\Api\AiChatMemoryController;
use App\Http\Controllers\Api\AiPmbDataController;
use App\Http\Controllers\Api\CampusSettingController;
use App\Http\Controllers\Api\PmbInformationSectionController;
use App\Http\Controllers\Api\PmbLandingContentController;
use App\Http\Controllers\Api\PmbRegistrationCascadeController;
use App\Http\Controllers\Api\PmbCbtController;
use App\Http\Controllers\Api\PmbLocalApplicationController;
use App\Http\Controllers\Api\DokuPaymentController;
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

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/me', [AuthController::class, 'me']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::put('/profile', [ProfileController::class, 'update']);
Route::put('/profile/password', [ProfileController::class, 'updatePassword']);
Route::post('/profile/photo', [ProfileController::class, 'updatePhoto']);
Route::delete('/profile/photo', [ProfileController::class, 'deletePhoto']);
Route::get('/registration/options', [PmbLocalApplicationController::class, 'options']);
Route::prefix('registration/cascade')->group(function (): void {
    Route::get('/jenjang', [PmbRegistrationCascadeController::class, 'jenjang']);
    Route::get('/program-studi', [PmbRegistrationCascadeController::class, 'programStudi']);
    Route::get('/lokasi', [PmbRegistrationCascadeController::class, 'lokasi']);
    Route::get('/jenis-pendaftaran', [PmbRegistrationCascadeController::class, 'jenisPendaftaran']);
    Route::get('/waktu-perkuliahan', [PmbRegistrationCascadeController::class, 'waktuPerkuliahan']);
    Route::get('/jalur-masuk', [PmbRegistrationCascadeController::class, 'jalurMasuk']);
    Route::get('/resolve', [PmbRegistrationCascadeController::class, 'resolve']);
});
Route::get('/registration', [PmbLocalApplicationController::class, 'show']);
Route::post('/registration/cascade', [PmbLocalApplicationController::class, 'storeCascade']);
Route::post('/registration', [PmbLocalApplicationController::class, 'store']);
Route::post('/registration/submit', [PmbLocalApplicationController::class, 'submit']);
Route::post('/registration/documents', [PmbLocalApplicationController::class, 'uploadDocument']);
Route::post('/registration/payment/create', [DokuPaymentController::class, 'create']);
Route::get('/registration/payment/status', [DokuPaymentController::class, 'status']);
Route::post('/webhooks/doku', [DokuPaymentController::class, 'webhook']);
Route::get('/registration/cbt', [PmbCbtController::class, 'show']);
Route::post('/registration/cbt/start', [PmbCbtController::class, 'start']);
Route::get('/registration/cbt/attempts/{attemptId}', [PmbCbtController::class, 'attempt']);
Route::post('/registration/cbt/attempts/{attemptId}/submit', [PmbCbtController::class, 'submit']);


Route::prefix('ai')
    ->middleware(['throttle:120,1'])
    ->group(function () {
    Route::post('/chat', [AiChatController::class, 'store']);
});

Route::prefix('ai')
    ->middleware(['ai.internal', 'throttle:120,1'])
    ->group(function () {
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
    Route::get('/pendaftaran-dibuka', [AiPmbDataController::class, 'openRegistrations']);
    Route::post('/memory/conversations', [AiChatMemoryController::class, 'ensureConversation']);
    Route::get('/memory/conversations/{conversationId}/messages', [AiChatMemoryController::class, 'messages']);
    Route::post('/memory/conversations/{conversationId}/messages', [AiChatMemoryController::class, 'storeMessage']);
    Route::post('/memory/conversations/{conversationId}/lead', [AiChatMemoryController::class, 'storeLead']);
});
