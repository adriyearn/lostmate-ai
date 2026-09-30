<?php

use App\Http\Controllers\AiMatchController;
use App\Http\Controllers\BrowseController;
use App\Http\Controllers\FoundItemController;
use App\Http\Controllers\LostItemController;
use App\Http\Controllers\MyReportsController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/browse', [BrowseController::class, 'index'])->name('browse.index');

    Route::get('/my-reports', [MyReportsController::class, 'index'])->name('my-reports.index');

    Route::get('/lost-items/create', [LostItemController::class, 'create'])->name('lost-items.create');
    Route::post('/lost-items', [LostItemController::class, 'store'])->name('lost-items.store');
    Route::get('/lost-items/{lostItem}', [LostItemController::class, 'show'])->name('lost-items.show');
    Route::get('/lost-items/{lostItem}/edit', [LostItemController::class, 'edit'])->name('lost-items.edit');
    Route::put('/lost-items/{lostItem}', [LostItemController::class, 'update'])->name('lost-items.update');
    Route::delete('/lost-items/{lostItem}', [LostItemController::class, 'destroy'])->name('lost-items.destroy');
    Route::get('/lost-items/{lostItem}/matches', [LostItemController::class, 'matches'])->name('lost-items.matches');
    Route::post('/lost-items/{lostItem}/rerun-matching', [LostItemController::class, 'rerunMatching'])->name('lost-items.rerun-matching');

    Route::get('/found-items/create', [FoundItemController::class, 'create'])->name('found-items.create');
    Route::post('/found-items', [FoundItemController::class, 'store'])->name('found-items.store');
    Route::get('/found-items/{foundItem}', [FoundItemController::class, 'show'])->name('found-items.show');
    Route::get('/found-items/{foundItem}/edit', [FoundItemController::class, 'edit'])->name('found-items.edit');
    Route::put('/found-items/{foundItem}', [FoundItemController::class, 'update'])->name('found-items.update');
    Route::delete('/found-items/{foundItem}', [FoundItemController::class, 'destroy'])->name('found-items.destroy');
    Route::get('/found-items/{foundItem}/matches', [FoundItemController::class, 'matches'])->name('found-items.matches');
    Route::post('/found-items/{foundItem}/rerun-matching', [FoundItemController::class, 'rerunMatching'])->name('found-items.rerun-matching');

    Route::post('/ai-matches/{aiMatch}/dismiss', [AiMatchController::class, 'dismiss'])->name('ai-matches.dismiss');
    Route::post('/ai-matches/{aiMatch}/start-conversation', [AiMatchController::class, 'startConversation'])->name('ai-matches.start-conversation');
});
