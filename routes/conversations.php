<?php

use App\Http\Controllers\ConversationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/messages', [ConversationController::class, 'index'])->name('conversations.index');
    Route::get('/messages/{conversation}', [ConversationController::class, 'show'])->name('conversations.show');
    Route::post('/messages/{conversation}', [ConversationController::class, 'storeMessage'])->middleware('throttle:30,1')->name('conversations.store-message');
    Route::get('/messages/{conversation}/poll', [ConversationController::class, 'poll'])->name('conversations.poll');

    Route::post('/lost-items/{lostItem}/contact', [ConversationController::class, 'startFromLostItem'])->middleware('throttle:10,1')->name('lost-items.contact');
    Route::post('/found-items/{foundItem}/contact', [ConversationController::class, 'startFromFoundItem'])->middleware('throttle:10,1')->name('found-items.contact');

    Route::post('/messages/{message}/report', [ReportController::class, 'reportMessage'])->middleware('throttle:10,1')->name('messages.report');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
});
