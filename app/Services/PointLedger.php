<?php

namespace App\Services;

use App\Models\PointTransaction;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PointLedger
{
    public function record(User $user, int $amount, string $type, ?Room $room = null, ?string $description = null): PointTransaction
    {
        return DB::transaction(function () use ($user, $amount, $type, $room, $description) {
            $user->refresh();
            $user->increment('points', $amount);

            return PointTransaction::create([
                'user_id' => $user->id,
                'room_id' => $room?->id,
                'amount' => $amount,
                'balance_after' => $user->points,
                'type' => $type,
                'description' => $description,
            ]);
        });
    }

    public function holdWager(Room $room, User $user, string $description): PointTransaction
    {
        return $this->record($user, -$room->wager_points, 'wager_hold', $room, $description);
    }

    public function settleRoom(Room $room, User $winner, User $loser): void
    {
        DB::transaction(function () use ($room, $winner, $loser) {
            $locked = Room::whereKey($room->id)->lockForUpdate()->first();

            if ($locked->status === 'completed') {
                return;
            }

            // Rooms accepted before the point-hold mechanic existed never had their wager
            // deducted at accept time, so there's nothing held in escrow to release for them -
            // fall back to the old direct win/loss transfer for those legacy in-flight rooms.
            $alreadyHeld = $locked->pointTransactions()->where('type', 'wager_hold')->exists();

            if ($alreadyHeld) {
                $this->record($winner, $room->wager_points * 2, 'wager_win', $room, "Menang duel vs {$loser->name}, poin taruhan dirilis");
            } else {
                $this->record($winner, $room->wager_points, 'wager_win', $room, "Menang duel vs {$loser->name}");
                $this->record($loser, -$room->wager_points, 'wager_loss', $room, "Kalah duel vs {$winner->name}");
            }

            $room->update([
                'winner_id' => $winner->id,
                'status' => 'completed',
            ]);
        });
    }
}
