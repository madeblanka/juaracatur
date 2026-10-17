<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class PointHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('points.history', [
            'user' => $user,
            'transactions' => $user->pointTransactions()->with('room')->latest('id')->paginate(30),
        ]);
    }
}
