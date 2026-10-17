<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VenueController extends Controller
{
    public function index(Request $request): View
    {
        $query = Venue::query()->where('is_active', true);

        if ($request->filled('q')) {
            $query->where('name', 'like', '%'.$request->query('q').'%');
        }

        $sort = $request->query('sort');

        match ($sort) {
            'wilayah' => $query->orderByRaw('wilayah IS NULL, wilayah asc'),
            default => $query->orderBy('name'),
        };

        $venues = $query
            ->withCount(['rooms as rooms_count' => fn ($q) => $q->inLobby()])
            ->withCount(['tournaments as ongoing_tournaments_count' => fn ($q) => $q->where('status', 'ongoing')])
            ->paginate(20)
            ->withQueryString();

        return view('venues.index', [
            'venues' => $venues,
            'q' => $request->query('q'),
            'sort' => $sort,
        ]);
    }

    public function show(Venue $venue): View
    {
        abort_unless($venue->is_active, 404);

        $lobby = Room::query()
            ->where('venue_id', $venue->id)
            ->inLobby()
            ->with(['challenger', 'opponent'])
            ->latest()
            ->get();

        return view('venues.show', [
            'venue' => $venue->load('photos'),
            'lobby' => $lobby,
        ]);
    }
}
