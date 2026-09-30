<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class MyReportsController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $lostItems = $user->lostItems()
            ->with('category')
            ->latest()
            ->paginate(12, ['*'], 'lost_page');

        $foundItems = $user->foundItems()
            ->with('category')
            ->latest()
            ->paginate(12, ['*'], 'found_page');

        return view('reports.my-reports', [
            'lostItems' => $lostItems,
            'foundItems' => $foundItems,
            'tab' => $request->query('tab', 'lost'),
        ]);
    }
}
