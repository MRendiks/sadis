<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FilesController;
use App\Http\Controllers\Login;
use App\Http\Controllers\UserAdminController;
use App\Http\Controllers\FolderManagementController;
use App\Http\Controllers\FileReviewController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;

// =======================
// AUTH (PUBLIC) ROUTES
// =======================
Route::get('/', [Login::class, 'index'])->name('index');
Route::post('/login', [Login::class, 'login'])->name('login');
Route::post('/logout', [Login::class, 'logout'])->name('logout');

// =======================
// PROTECTED ROUTES
// =======================
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');

    // =======================
    // FILES (umum)
    // =======================
    Route::get('/files', [FilesController::class,'index'])->name('files.index');
    Route::get('/files/{id}/preview', [FilesController::class,'preview'])->name('files.preview')->middleware(['auth']);
    Route::get('/files/{id}/download', [FilesController::class,'download'])->name('files.download');

    // Create/Store dibatasi Policy: submit (user_bidang, admin_arsip, super_admin)
    Route::middleware('can:submit,App\Models\FileEntry')->group(function () {
        Route::get('/files/create', [FilesController::class,'create'])->name('files.create');
        Route::post('/files', [FilesController::class,'store'])->name('files.store');
    });

    // Edit/Update/Delete tetap authorize per-file di controller/policy
    Route::get('/files/{id}/edit', [FilesController::class,'edit'])->name('files.edit');
    Route::put('/files/{id}', [FilesController::class,'update'])->name('files.update');
    Route::delete('/files/{id}', [FilesController::class,'destroy'])->name('files.destroy');

    // =======================
    // FOLDER MANAGEMENT — khusus super_admin
    // (pakai middleware role: ... karena alias-mu adalah 'role')
    // =======================
    Route::get('/folders', [FolderManagementController::class,'index'])->name('folders.index');
    Route::get('/folders/create', [FolderManagementController::class,'create'])->name('folders.create');
    Route::post('/folders', [FolderManagementController::class,'store'])->name('folders.store');
    Route::delete('/folders/{folder}', [FolderManagementController::class,'destroy'])->name('folders.destroy');

    // =======================
    // REVIEW QUEUE — admin_arsip & super_admin
    // Akses halaman queue: cukup class-level policy 'review'
    // =======================
    Route::middleware('can:review,App\Models\FileEntry')->group(function () {
        Route::get('/reviews/queue', [FileReviewController::class,'queue'])->name('reviews.queue');
    });

    // Aksi per-file: pakai instance policy 'can:review,file'
    Route::post('/reviews/{file}/take',    [FileReviewController::class,'take'])
        ->middleware('can:review,file')->name('reviews.take');

    Route::post('/reviews/{file}/approve', [FileReviewController::class,'approve'])
        ->middleware('can:review,file')->name('reviews.approve');

    Route::post('/reviews/{file}/reject',  [FileReviewController::class,'reject'])
        ->middleware('can:review,file')->name('reviews.reject');

    // =======================
    // USER ADMIN (contoh)
    // =======================
    Route::get('/admin/users', [UserAdminController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/users/create', [UserAdminController::class, 'create'])->name('admin.users.create');
    Route::post('/admin/users', [UserAdminController::class, 'store'])->name('admin.users.store');
    Route::get('/admin/users/{id}/edit', [UserAdminController::class, 'edit'])->name('admin.users.edit');
    Route::put('/admin/users/{id}', [UserAdminController::class, 'update'])->name('admin.users.update');
    Route::delete('/admin/users/{id}', [UserAdminController::class, 'destroy'])->name('admin.users.destroy');
});
