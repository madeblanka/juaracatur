<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Tournament;
use App\Models\User;
use App\Models\Venue;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'totalPlayers' => User::where('role', 'player')->count(),
            'openRooms' => Room::where('status', 'open')->count(),
            'disputedRooms' => Room::where('status', 'disputed')->count(),
            'totalVenues' => Venue::count(),
            'ongoingTournaments' => Tournament::where('status', 'ongoing')->count(),
            'recentRooms' => Room::with(['challenger', 'opponent'])->latest()->limit(8)->get(),
        ]);
    }
}
