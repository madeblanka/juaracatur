<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\TournamentParticipant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlayerController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()->where('role', 'player')->active();

        if ($request->filled('q')) {
            $search = $request->query('q');
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
        }

        $sort = $request->query('sort');

        match ($sort) {
            'wilayah' => $query->orderByRaw('wilayah IS NULL, wilayah asc'),
            'email' => $query->orderBy('email'),
            default => $query->latest(),
        };

        $players = $query->paginate(30)->withQueryString();

        return view('players.index', [
            'players' => $players,
            'q' => $request->query('q'),
            'sort' => $sort,
        ]);
    }

    public function me(Request $request): View
    {
        return $this->show($request->user());
    }

    public function show(User $user): View
    {
        $wins = $user->wonRooms()->count();
        $totalMatches = $user->completedRoomsCount();
        $losses = $totalMatches - $wins;

        $recentMatches = Room::query()
            ->where('status', 'completed')
            ->where(fn ($q) => $q->where('challenger_id', $user->id)->orWhere('opponent_id', $user->id))
            ->with(['challenger', 'opponent', 'venue', 'winner'])
            ->latest('updated_at')
            ->paginate(15);

        $tournamentHistory = TournamentParticipant::query()
            ->where('user_id', $user->id)
            ->with('tournament')
            ->get();

        return view('players.show', [
            'player' => $user,
            'wins' => $wins,
            'losses' => $losses,
            'totalMatches' => $totalMatches,
            'recentMatches' => $recentMatches,
            'tournamentHistory' => $tournamentHistory,
        ]);
    }
}
