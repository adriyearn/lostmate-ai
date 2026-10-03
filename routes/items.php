<?php

use App\Http\Controllers\AiMatchController;
use App\Http\Controllers\BrowseController;
use App\Http\Controllers\FoundItemController;
use App\Http\Controllers\LostItemController;
use App\Http\Controllers\MyReportsController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/users/{user}', [UserProfileController::class, 'show'])->name('users.show');
    Route::put('/password', [PasswordController::class, 'update'])->middleware('throttle:6,1')->name('password.update');

    Route::get('/browse', [BrowseController::class, 'index'])->name('browse.index');

    Route::get('/my-reports', [MyReportsController::class, 'index'])->name('my-reports.index');

    Route::get('/lost-items/create', [LostItemController::class, 'create'])->name('lost-items.create');
    Route::post('/lost-items', [LostItemController::class, 'store'])->middleware('throttle:10,1')->name('lost-items.store');
    Route::get('/lost-items/{lostItem}', [LostItemController::class, 'show'])->name('lost-items.show');
    Route::get('/lost-items/{lostItem}/edit', [LostItemController::class, 'edit'])->name('lost-items.edit');
    Route::put('/lost-items/{lostItem}', [LostItemController::class, 'update'])->name('lost-items.update');
    Route::delete('/lost-items/{lostItem}', [LostItemController::class, 'destroy'])->name('lost-items.destroy');
    Route::get('/lost-items/{lostItem}/matches', [LostItemController::class, 'matches'])->name('lost-items.matches');
    Route::post('/lost-items/{lostItem}/rerun-matching', [LostItemController::class, 'rerunMatching'])->name('lost-items.rerun-matching');
    Route::post('/lost-items/{lostItem}/report', [ReportController::class, 'reportLostItem'])->middleware('throttle:10,1')->name('lost-items.report');

    Route::get('/found-items/create', [FoundItemController::class, 'create'])->name('found-items.create');
    Route::post('/found-items', [FoundItemController::class, 'store'])->middleware('throttle:10,1')->name('found-items.store');
    Route::get('/found-items/{foundItem}', [FoundItemController::class, 'show'])->name('found-items.show');
    Route::get('/found-items/{foundItem}/edit', [FoundItemController::class, 'edit'])->name('found-items.edit');
    Route::put('/found-items/{foundItem}', [FoundItemController::class, 'update'])->name('found-items.update');
    Route::delete('/found-items/{foundItem}', [FoundItemController::class, 'destroy'])->name('found-items.destroy');
    Route::get('/found-items/{foundItem}/matches', [FoundItemController::class, 'matches'])->name('found-items.matches');
    Route::post('/found-items/{foundItem}/rerun-matching', [FoundItemController::class, 'rerunMatching'])->name('found-items.rerun-matching');
    Route::post('/found-items/{foundItem}/report', [ReportController::class, 'reportFoundItem'])->middleware('throttle:10,1')->name('found-items.report');

    Route::post('/ai-matches/{aiMatch}/dismiss', [AiMatchController::class, 'dismiss'])->name('ai-matches.dismiss');
    Route::post('/ai-matches/{aiMatch}/start-conversation', [AiMatchController::class, 'startConversation'])->name('ai-matches.start-conversation');
});
