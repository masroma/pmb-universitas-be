<?php

use App\Http\Controllers\Admin\AiChatLeadController;
use App\Http\Controllers\Admin\AiDashboardController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CampusSettingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MasterPmbController;
use App\Http\Controllers\Admin\PmbCatalogController;
use App\Http\Controllers\Admin\PmbInformationSectionController;
use App\Http\Controllers\Admin\PmbLocalApplicationController;
use App\Http\Controllers\Admin\ProfileController;
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
    Route::get('/dashboard-ai', AiDashboardController::class)->middleware('admin.role:super_admin,admin_pmb,operator_crm')->name('ai-dashboard');
    Route::get('/pendaftaran-lokal/export', [PmbLocalApplicationController::class, 'export'])->middleware('admin.role:super_admin,admin_pmb')->name('local-applications.export');
    Route::get('/pendaftaran-lokal', [PmbLocalApplicationController::class, 'index'])->middleware('admin.role:super_admin,admin_pmb')->name('local-applications.index');
    Route::get('/pendaftaran-lokal/{application}', [PmbLocalApplicationController::class, 'show'])->middleware('admin.role:super_admin,admin_pmb')->name('local-applications.show');
    Route::put('/pendaftaran-lokal/{application}/status', [PmbLocalApplicationController::class, 'updateStatus'])->middleware('admin.role:super_admin,admin_pmb')->name('local-applications.status.update');
    Route::get('/crm-ai/export', [AiChatLeadController::class, 'export'])->middleware('admin.role:super_admin,admin_pmb,operator_crm')->name('ai-chat-leads.export');
    Route::get('/crm-ai', [AiChatLeadController::class, 'index'])->middleware('admin.role:super_admin,admin_pmb,operator_crm')->name('ai-chat-leads.index');
    Route::get('/crm-ai/{lead}', [AiChatLeadController::class, 'show'])->middleware('admin.role:super_admin,admin_pmb,operator_crm')->name('ai-chat-leads.show');
    Route::put('/crm-ai/{lead}/follow-up', [AiChatLeadController::class, 'updateFollowUp'])->middleware('admin.role:super_admin,admin_pmb,operator_crm')->name('ai-chat-leads.follow-up.update');
    Route::middleware('admin.role:super_admin,admin_pmb')->prefix('/master-pmb')->name('master-pmb.')->group(function (): void {
        Route::get('/{resource}', [MasterPmbController::class, 'index'])->name('index');
        Route::get('/{resource}/create', [MasterPmbController::class, 'create'])->name('create');
        Route::post('/{resource}', [MasterPmbController::class, 'store'])->name('store');
        Route::get('/{resource}/{id}/edit', [MasterPmbController::class, 'edit'])->name('edit');
        Route::put('/{resource}/{id}', [MasterPmbController::class, 'update'])->name('update');
        Route::delete('/{resource}/{id}', [MasterPmbController::class, 'destroy'])->name('destroy');
    });
    Route::get('/pendaftaran-dibuka', [PmbCatalogController::class, 'openedRegistrations'])->middleware('admin.role:super_admin,admin_pmb')->name('pmb-catalog.opened-registrations');
    Route::redirect('/pendaftar', '/admin/pendaftaran-lokal')->name('pmb-catalog.applicants');
    Route::get('/program-studi', [PmbCatalogController::class, 'studyPrograms'])->middleware('admin.role:super_admin,admin_pmb')->name('pmb-catalog.study-programs');
    Route::get('/periode', [PmbCatalogController::class, 'periods'])->middleware('admin.role:super_admin,admin_pmb')->name('pmb-catalog.periods');
    Route::put('/periode/{period}/brochure', [PmbCatalogController::class, 'updatePeriodBrochure'])->middleware('admin.role:super_admin,admin_pmb')->name('pmb-catalog.periods.brochure.update');
    Route::resource('/konten-pmb', PmbInformationSectionController::class)
        ->except('show')
        ->middleware('admin.role:super_admin,admin_pmb')
        ->names('pmb-information')
        ->parameters(['konten-pmb' => 'pmbInformation']);
    Route::resource('/biaya-kuliah', TuitionFeeController::class)
        ->except('show')
        ->middleware('admin.role:super_admin,admin_pmb')
        ->names('tuition-fees')
        ->parameters(['biaya-kuliah' => 'tuitionFee']);
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/password', [ProfileController::class, 'editPassword'])->name('profile.password.edit');
    Route::put('/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::get('/settings', [CampusSettingController::class, 'edit'])->middleware('admin.role:super_admin,admin_pmb')->name('settings.edit');
    Route::put('/settings', [CampusSettingController::class, 'update'])->middleware('admin.role:super_admin,admin_pmb')->name('settings.update');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
