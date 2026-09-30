<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class MyClaimController extends Controller
{
    public function index(Request $request): View
    {
        $claims = $request->user()->claims()
            ->with(['foundItem.category', 'foundItem.images', 'lostItem'])
            ->latest()
            ->paginate(12);

        return view('claims.my-claims', ['claims' => $claims]);
    }
}
