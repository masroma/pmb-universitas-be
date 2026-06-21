<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\AiChatLeadController;
use App\Http\Controllers\Admin\CampusSettingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PmbCatalogController;
use App\Http\Controllers\Admin\PmbInformationSectionController;
use App\Http\Controllers\Admin\PmbLocalApplicationController;
use App\Http\Controllers\Admin\TuitionFeeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::redirect('/login', '/admin/login')->name('login');
Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.store');

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/pendaftaran-lokal', [PmbLocalApplicationController::class, 'index'])->name('local-applications.index');
    Route::get('/pendaftaran-lokal/{application}', [PmbLocalApplicationController::class, 'show'])->name('local-applications.show');
    Route::put('/pendaftaran-lokal/{application}/status', [PmbLocalApplicationController::class, 'updateStatus'])->name('local-applications.status.update');
    Route::get('/crm-ai', [AiChatLeadController::class, 'index'])->name('ai-chat-leads.index');
    Route::get('/crm-ai/{lead}', [AiChatLeadController::class, 'show'])->name('ai-chat-leads.show');
    Route::put('/crm-ai/{lead}/follow-up', [AiChatLeadController::class, 'updateFollowUp'])->name('ai-chat-leads.follow-up.update');
    Route::get('/pendaftaran-dibuka', [PmbCatalogController::class, 'openedRegistrations'])->name('pmb-catalog.opened-registrations');
    Route::get('/pendaftar', [PmbCatalogController::class, 'applicants'])->name('pmb-catalog.applicants');
    Route::get('/program-studi', [PmbCatalogController::class, 'studyPrograms'])->name('pmb-catalog.study-programs');
    Route::get('/periode', [PmbCatalogController::class, 'periods'])->name('pmb-catalog.periods');
    Route::put('/periode/{period}/brochure', [PmbCatalogController::class, 'updatePeriodBrochure'])->name('pmb-catalog.periods.brochure.update');
    Route::resource('/konten-pmb', PmbInformationSectionController::class)
        ->except('show')
        ->names('pmb-information')
        ->parameters(['konten-pmb' => 'pmbInformation']);
    Route::resource('/biaya-kuliah', TuitionFeeController::class)
        ->except('show')
        ->names('tuition-fees')
        ->parameters(['biaya-kuliah' => 'tuitionFee']);
    Route::get('/settings', [CampusSettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [CampusSettingController::class, 'update'])->name('settings.update');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
