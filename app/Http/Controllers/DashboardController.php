<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $myRooms = Room::query()
            ->where('challenger_id', $user->id)
            ->orWhere('opponent_id', $user->id)
            ->with(['challenger', 'opponent', 'venue'])
            ->latest()
            ->limit(10)
            ->get();

        $openChallenges = Room::query()
            ->where('status', 'open')
            ->where('challenger_id', '!=', $user->id)
            ->where(fn ($query) => $query->whereNull('opponent_id')->orWhere('opponent_id', $user->id))
            ->with(['challenger', 'venue'])
            ->latest()
            ->limit(10)
            ->get();

        $transactions = $user->pointTransactions()->latest()->limit(10)->get();

        return view('dashboard', [
            'user' => $user,
            'myRooms' => $myRooms,
            'openChallenges' => $openChallenges,
            'transactions' => $transactions,
        ]);
    }
}
