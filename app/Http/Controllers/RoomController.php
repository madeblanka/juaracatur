<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\User;
use App\Models\Venue;
use App\Notifications\ChallengeAccepted;
use App\Notifications\ResultReported;
use App\Notifications\RoomInvite;
use App\Notifications\RoomSettled;
use App\Services\PointLedger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $openChallenges = Room::query()
            ->where('status', 'open')
            ->where('challenger_id', '!=', $user->id)
            ->where(fn ($query) => $query->whereNull('opponent_id')->orWhere('opponent_id', $user->id))
            ->with(['challenger', 'venue'])
            ->latest()
            ->get();

        $myRooms = Room::query()
            ->where('challenger_id', $user->id)
            ->orWhere('opponent_id', $user->id)
            ->with(['challenger', 'opponent', 'venue'])
            ->latest()
            ->get();

        return view('rooms.index', [
            'openChallenges' => $openChallenges,
            'myRooms' => $myRooms,
        ]);
    }

    public function create(Request $request): View
    {
        $venues = Venue::where('is_active', true)->orderBy('name')->get();
        $players = User::where('role', 'player')->active()->where('id', '!=', $request->user()->id)->orderBy('name')->get();

        return view('rooms.create', [
            'venues' => $venues,
            'players' => $players,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->points < 1) {
            return back()->with('error', 'Poinmu tidak cukup untuk membuat tantangan baru. Selesaikan atau tunggu room lain yang sedang berlangsung.');
        }

        $data = $request->validate([
            'opponent_id' => ['nullable', 'exists:users,id', 'not_in:'.$user->id],
            'venue_id' => ['required', 'exists:venues,id'],
            'wager_points' => ['required', 'integer', 'min:1', 'max:'.$user->points],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $room = Room::create([
            'code' => strtoupper(Str::random(6)),
            'challenger_id' => $user->id,
            'opponent_id' => $data['opponent_id'] ?? null,
            'venue_id' => $data['venue_id'],
            'wager_points' => $data['wager_points'],
            'status' => 'open',
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('rooms.show', $room)->with('success', 'Room tantangan berhasil dibuat. Jangan lupa bawa papan catur sendiri!');
    }

    public function show(Request $request, Room $room): View
    {
        $room->load(['challenger', 'opponent', 'venue', 'winner', 'pointTransactions.user']);

        $invitablePlayers = User::where('role', 'player')->active()
            ->where('id', '!=', $room->challenger_id)
            ->orderBy('name')
            ->get();

        return view('rooms.show', [
            'room' => $room,
            'invitablePlayers' => $invitablePlayers,
        ]);
    }

    public function accept(Request $request, Room $room, PointLedger $ledger): RedirectResponse
    {
        $user = $request->user();

        $result = DB::transaction(function () use ($room, $user, $ledger) {
            $locked = Room::whereKey($room->id)->lockForUpdate()->first();

            abort_unless($locked->status === 'open', 403, 'Room ini sudah tidak bisa diterima.');
            abort_if($locked->challenger_id === $user->id, 403, 'Kamu tidak bisa menerima tantanganmu sendiri.');
            abort_if($locked->opponent_id !== null && $locked->opponent_id !== $user->id, 403, 'Tantangan ini ditujukan untuk pemain lain.');

            // Lock both players' rows (consistent ascending-id order to avoid deadlocking
            // against another concurrent accept()) and re-check their LIVE balance, not the
            // snapshot from when the room was created - the challenger's points may have since
            // dropped below the wager because they're already holding a stake in another room.
            $userIds = collect([$locked->challenger_id, $user->id])->sort()->values();
            $lockedUsers = User::whereIn('id', $userIds)->lockForUpdate()->get()->keyBy('id');
            $challenger = $lockedUsers[$locked->challenger_id];
            $opponent = $lockedUsers[$user->id];

            if ($opponent->points < $locked->wager_points) {
                return 'insufficient_points';
            }

            if ($challenger->points < $locked->wager_points) {
                return 'challenger_insufficient_points';
            }

            $ledger->holdWager($locked, $challenger, "Poin ditahan untuk duel vs {$opponent->name}");
            $ledger->holdWager($locked, $opponent, "Poin ditahan untuk duel vs {$challenger->name}");

            $locked->update([
                'opponent_id' => $user->id,
                'status' => 'accepted',
            ]);

            return 'ok';
        });

        if ($result === 'insufficient_points') {
            return back()->with('error', 'Poinmu tidak cukup untuk menerima tantangan ini.');
        }

        if ($result === 'challenger_insufficient_points') {
            return back()->with('error', 'Tantangan ini tidak bisa diterima karena poin penantang tidak lagi cukup (mungkin sedang ditahan di room lain).');
        }

        $room->refresh();
        $room->challenger->notify(new ChallengeAccepted($room));

        return redirect()->route('rooms.show', $room)->with('success', 'Tantangan diterima! Poin taruhan kedua pemain sudah ditahan di room ini. Jangan lupa bawa papan catur sendiri ke tempat bertanding.');
    }

    public function cancel(Request $request, Room $room): RedirectResponse
    {
        abort_unless($room->challenger_id === $request->user()->id, 403);
        abort_unless($room->status === 'open', 403, 'Room yang sudah diterima tidak bisa dibatalkan.');

        $room->update(['status' => 'cancelled']);

        return redirect()->route('rooms.index')->with('success', 'Room dibatalkan.');
    }

    public function report(Request $request, Room $room, PointLedger $ledger): RedirectResponse
    {
        $user = $request->user();

        abort_unless($room->status === 'accepted', 403, 'Hasil hanya bisa dilaporkan untuk room yang sedang berlangsung.');
        abort_unless($room->hasParticipant($user->id), 403);

        $data = $request->validate([
            'winner_id' => ['required', 'integer', 'in:'.$room->challenger_id.','.$room->opponent_id],
        ]);

        $winner = null;
        $loser = null;

        $outcome = DB::transaction(function () use ($room, $user, $data, $ledger, &$winner, &$loser) {
            $locked = Room::whereKey($room->id)->lockForUpdate()->first();

            abort_unless($locked->status === 'accepted', 403, 'Hasil hanya bisa dilaporkan untuk room yang sedang berlangsung.');

            if ($user->id === $locked->challenger_id) {
                $locked->update(['challenger_reported_winner_id' => $data['winner_id']]);
            } else {
                $locked->update(['opponent_reported_winner_id' => $data['winner_id']]);
            }

            $locked->refresh();

            if ($locked->challenger_reported_winner_id === null || $locked->opponent_reported_winner_id === null) {
                return 'waiting';
            }

            if ((int) $locked->challenger_reported_winner_id !== (int) $locked->opponent_reported_winner_id) {
                $locked->update(['status' => 'disputed']);

                return 'disputed';
            }

            $winner = $locked->challenger_reported_winner_id === $locked->challenger_id ? $locked->challenger : $locked->opponent;
            $loser = $winner->id === $locked->challenger_id ? $locked->opponent : $locked->challenger;

            $ledger->settleRoom($locked, $winner, $loser);

            return 'settled';
        });

        if ($outcome === 'waiting') {
            $other = $user->id === $room->challenger_id ? $room->opponent : $room->challenger;
            $other->notify(new ResultReported($room, $user));
        }

        if ($outcome === 'settled') {
            $winner->notify(new RoomSettled($room, won: true, pointsChanged: $room->wager_points));
            $loser->notify(new RoomSettled($room, won: false, pointsChanged: -$room->wager_points));
        }

        return match ($outcome) {
            'waiting' => redirect()->route('rooms.show', $room)->with('success', 'Laporan hasil dicatat. Menunggu konfirmasi lawan.'),
            'disputed' => redirect()->route('rooms.show', $room)->with('error', 'Laporan hasil berbeda. Room ditandai sengketa dan akan ditinjau admin.'),
            default => redirect()->route('rooms.show', $room)->with('success', 'Hasil dikonfirmasi kedua pihak. Poin sudah dipindahkan.'),
        };
    }

    public function invite(Request $request, Room $room): RedirectResponse
    {
        abort_unless($room->challenger_id === $request->user()->id, 403);
        abort_unless($room->status === 'open', 403, 'Room ini sudah tidak terbuka untuk diundang.');

        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id', 'not_in:'.$room->challenger_id],
        ]);

        $invitee = User::findOrFail($data['user_id']);
        $invitee->notify(new RoomInvite($room));

        return back()->with('success', "Undangan terkirim ke {$invitee->name}.");
    }

    public function messages(Request $request, Room $room): JsonResponse
    {
        abort_unless($room->hasParticipant($request->user()->id), 403);

        $query = $room->messages()->with('user')->orderBy('id');

        if ($request->filled('after')) {
            $query->where('id', '>', (int) $request->query('after'));
        }

        return response()->json($query->get()->map(fn ($m) => [
            'id' => $m->id,
            'user_id' => $m->user_id,
            'user_name' => $m->user->name,
            'message' => $m->message,
            'time' => $m->created_at->format('H:i'),
        ]));
    }

    public function sendMessage(Request $request, Room $room): JsonResponse|RedirectResponse
    {
        abort_unless($room->hasParticipant($request->user()->id), 403);

        $data = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $message = $room->messages()->create([
            'user_id' => $request->user()->id,
            'message' => $data['message'],
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'id' => $message->id,
                'user_id' => $message->user_id,
                'user_name' => $request->user()->name,
                'message' => $message->message,
                'time' => $message->created_at->format('H:i'),
            ]);
        }

        return back();
    }
}
