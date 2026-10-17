<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Notifications\RoomSettled;
use App\Services\PointLedger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function index(): View
    {
        $rooms = Room::query()
            ->whereIn('status', ['disputed', 'accepted'])
            ->with(['challenger', 'opponent', 'venue'])
            ->latest()
            ->get();

        return view('admin.rooms.index', ['rooms' => $rooms]);
    }

    public function resolve(Request $request, Room $room, PointLedger $ledger): RedirectResponse
    {
        abort_unless(in_array($room->status, ['disputed', 'accepted'], true), 403);

        $data = $request->validate([
            'winner_id' => ['required', 'integer', 'in:'.$room->challenger_id.','.$room->opponent_id],
        ]);

        $winner = (int) $data['winner_id'] === $room->challenger_id ? $room->challenger : $room->opponent;
        $loser = $winner->id === $room->challenger_id ? $room->opponent : $room->challenger;

        $ledger->settleRoom($room, $winner, $loser);

        $winner->notify(new RoomSettled($room, won: true, pointsChanged: $room->wager_points));
        $loser->notify(new RoomSettled($room, won: false, pointsChanged: -$room->wager_points));

        return redirect()->route('admin.rooms.index')->with('success', 'Sengketa room berhasil diselesaikan.');
    }
}
