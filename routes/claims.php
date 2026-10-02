<?php

use App\Http\Controllers\ClaimController;
use App\Http\Controllers\FoundItemController;
use App\Http\Controllers\LostItemController;
use App\Http\Controllers\MyClaimController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/found-items/{foundItem}/claim', [ClaimController::class, 'create'])->name('claims.create');
    Route::post('/found-items/{foundItem}/claim', [ClaimController::class, 'store'])->middleware('throttle:5,1')->name('claims.store');
    Route::get('/found-items/{foundItem}/claims', [FoundItemController::class, 'claims'])->name('found-items.claims');
    Route::post('/found-items/{foundItem}/withdraw', [FoundItemController::class, 'withdraw'])->name('found-items.withdraw');

    Route::post('/claims/{claim}/approve', [ClaimController::class, 'approve'])->name('claims.approve');
    Route::post('/claims/{claim}/reject', [ClaimController::class, 'reject'])->name('claims.reject');
    Route::post('/claims/{claim}/cancel', [ClaimController::class, 'cancel'])->name('claims.cancel');
    Route::post('/claims/{claim}/confirm-returned', [ClaimController::class, 'confirmReturned'])->name('claims.confirm-returned');

    Route::get('/my-claims', [MyClaimController::class, 'index'])->name('my-claims.index');

    Route::post('/lost-items/{lostItem}/withdraw', [LostItemController::class, 'withdraw'])->name('lost-items.withdraw');
    Route::post('/lost-items/{lostItem}/received', [LostItemController::class, 'markReceived'])->name('lost-items.received');
});
