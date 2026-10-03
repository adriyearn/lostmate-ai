<?php

use App\Http\Controllers\Admin\AdminLogController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ClaimController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\FlaggedContentController;
use App\Http\Controllers\Admin\ItemReportController;
use App\Http\Controllers\Admin\OfficeController;
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::post('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
    Route::post('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.update-role');

    Route::get('/reports', [ItemReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/lost/{lostItem}', [ItemReportController::class, 'showLost'])->name('reports.show-lost');
    Route::get('/reports/found/{foundItem}', [ItemReportController::class, 'showFound'])->name('reports.show-found');
    Route::post('/reports/lost/{lostItem}/close', [ItemReportController::class, 'closeLost'])->name('reports.close-lost');
    Route::post('/reports/found/{foundItem}/close', [ItemReportController::class, 'closeFound'])->name('reports.close-found');
    Route::delete('/reports/lost/{lostItem}', [ItemReportController::class, 'destroyLost'])->name('reports.destroy-lost');
    Route::delete('/reports/found/{foundItem}', [ItemReportController::class, 'destroyFound'])->name('reports.destroy-found');

    Route::get('/claims', [ClaimController::class, 'index'])->name('claims.index');
    Route::get('/claims/{claim}', [ClaimController::class, 'show'])->name('claims.show');
    Route::post('/claims/{claim}/override', [ClaimController::class, 'overrideStatus'])->name('claims.override');

    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::post('/categories/{category}/toggle-active', [CategoryController::class, 'toggleActive'])->name('categories.toggle-active');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('/flags', [FlaggedContentController::class, 'index'])->name('flags.index');
    Route::get('/flags/{report}', [FlaggedContentController::class, 'show'])->name('flags.show');
    Route::post('/flags/{report}/status', [FlaggedContentController::class, 'updateStatus'])->name('flags.update-status');

    Route::get('/logs', [AdminLogController::class, 'index'])->name('logs.index');

    Route::post('/system/retry-failed', [SystemController::class, 'retryFailed'])->name('system.retry-failed');

    Route::get('/office', [OfficeController::class, 'index'])->name('office.index');
    Route::post('/office/receive/{foundItem}', [OfficeController::class, 'receive'])->name('office.receive');
    Route::get('/office/tag/{foundItem}', [OfficeController::class, 'tag'])->name('office.tag');
    Route::post('/office/close-unclaimed/{foundItem}', [OfficeController::class, 'closeUnclaimed'])->name('office.close-unclaimed');

    Route::get('/exports', [ExportController::class, 'index'])->name('exports.index');
    Route::get('/exports/csv', [ExportController::class, 'csv'])->middleware('throttle:10,1')->name('exports.csv');
});
