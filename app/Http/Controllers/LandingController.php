<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Tournament;
use App\Models\User;
use App\Models\Venue;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function index(): View
    {
        $now = now();

        $upcomingTournaments = Tournament::query()
            ->where('status', 'ongoing')
            ->where('starts_at', '>', $now)
            ->withCount('participants')
            ->orderBy('starts_at')
            ->limit(10)
            ->get();

        $ongoingTournaments = Tournament::query()
            ->where('status', 'ongoing')
            ->where('starts_at', '<=', $now)
            ->withCount('participants')
            ->orderByDesc('starts_at')
            ->limit(10)
            ->get();

        $openChallengers = Room::query()
            ->where('status', 'open')
            ->with(['challenger', 'opponent', 'venue'])
            ->latest()
            ->limit(10)
            ->get();

        $liveRooms = Room::query()
            ->where('status', 'accepted')
            ->with(['challenger', 'opponent', 'venue'])
            ->latest('updated_at')
            ->limit(10)
            ->get();

        $activeVenues = Venue::query()
            ->where('is_active', true)
            ->whereHas('tournaments', fn ($q) => $q->where('status', 'ongoing'))
            ->withCount(['tournaments as ongoing_tournaments_count' => fn ($q) => $q->where('status', 'ongoing')])
            ->limit(10)
            ->get();

        $newParticipants = User::query()
            ->where('role', 'player')->active()
            ->latest()
            ->limit(10)
            ->get();

        $topRanking = User::query()
            ->where('role', 'player')->active()
            ->withCount([
                'wonRooms as wins',
                'challengedRooms as challenged_completed' => fn ($q) => $q->where('status', 'completed'),
                'joinedRooms as joined_completed' => fn ($q) => $q->where('status', 'completed'),
            ])
            ->get()
            ->map(function (User $player) {
                $player->total_matches = $player->challenged_completed + $player->joined_completed;
                $player->winrate = $player->total_matches > 0 ? $player->wins / $player->total_matches : 0;

                return $player;
            })
            ->filter(fn (User $player) => $player->total_matches > 0)
            ->sort(fn (User $a, User $b) => $b->winrate <=> $a->winrate ?: $b->wins <=> $a->wins)
            ->take(10)
            ->values();

        return view('welcome', [
            'upcomingTournaments' => $upcomingTournaments,
            'ongoingTournaments' => $ongoingTournaments,
            'openChallengers' => $openChallengers,
            'liveRooms' => $liveRooms,
            'activeVenues' => $activeVenues,
            'newParticipants' => $newParticipants,
            'topRanking' => $topRanking,
        ]);
    }
}
