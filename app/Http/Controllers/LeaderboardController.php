<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaderboardController extends Controller
{
    private const SORTS = [
        'pemain' => 'name',
        'poin' => 'points',
        'menang' => 'wins',
        'kalah' => '(challenged_completed + joined_completed - wins)',
        'total' => '(challenged_completed + joined_completed)',
    ];

    public function index(Request $request): View
    {
        $sort = array_key_exists($request->query('sort'), self::SORTS) ? $request->query('sort') : 'poin';
        $dir = $request->query('dir') === 'asc' ? 'asc' : 'desc';

        $players = User::query()
            ->where('role', 'player')->active()
            ->withCount([
                'wonRooms as wins',
                'challengedRooms as challenged_completed' => fn ($q) => $q->where('status', 'completed'),
                'joinedRooms as joined_completed' => fn ($q) => $q->where('status', 'completed'),
            ])
            ->orderByRaw(self::SORTS[$sort].' '.$dir)
            ->orderBy('id')
            ->paginate(50)
            ->withQueryString();

        return view('leaderboard.index', [
            'players' => $players,
            'sort' => $sort,
            'dir' => $dir,
        ]);
    }
}
