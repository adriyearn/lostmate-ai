<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth')->name('dashboard');

require __DIR__.'/items.php';
require __DIR__.'/conversations.php';
require __DIR__.'/claims.php';
require __DIR__.'/admin.php';
require __DIR__.'/auth.php';
