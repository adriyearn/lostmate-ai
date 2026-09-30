<?php

namespace App\Http\Controllers;

use App\Models\FoundItem;
use App\Models\LostItem;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('dashboard', [
            'recentLostItems' => LostItem::with('category', 'images')->latest()->take(6)->get(),
            'recentFoundItems' => FoundItem::with('category', 'images')->latest()->take(6)->get(),
        ]);
    }
}
