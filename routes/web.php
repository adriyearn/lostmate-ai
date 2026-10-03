<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::view('/privacy', 'privacy')->name('privacy');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/items.php';
require __DIR__.'/conversations.php';
require __DIR__.'/claims.php';
require __DIR__.'/admin.php';
require __DIR__.'/auth.php';
