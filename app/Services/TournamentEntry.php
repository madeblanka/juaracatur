<?php

namespace App\Services;

use App\Models\Tournament;
use App\Models\TournamentParticipant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TournamentEntry
{
    public function __construct(private readonly PointLedger $ledger)
    {
    }

    public function join(Tournament $tournament, User $user): TournamentParticipant
    {
        return DB::transaction(function () use ($tournament, $user) {
            $locked = Tournament::whereKey($tournament->id)->lockForUpdate()->first();
            $player = User::whereKey($user->id)->lockForUpdate()->first();

            if ($locked->status !== 'draft') {
                throw ValidationException::withMessages(['tournament' => 'Pendaftaran turnamen ini sudah ditutup.']);
            }

            if ($locked->participants()->where('user_id', $player->id)->exists()) {
                throw ValidationException::withMessages(['tournament' => 'Kamu sudah terdaftar di turnamen ini.']);
            }

            $fee = (int) $locked->entry_points;

            if ($fee > 0) {
                if ($player->points < $fee) {
                    throw ValidationException::withMessages(['tournament' => "Poinmu tidak cukup. Biaya daftar turnamen ini {$fee} poin."]);
                }

                $this->ledger->record($player, -$fee, 'tournament_entry', null, "Biaya daftar turnamen {$locked->name}");

                if ($locked->creator?->isVendor()) {
                    $this->ledger->record($locked->creator, $fee, 'tournament_entry_received', null, "{$player->name} mendaftar turnamen {$locked->name}");
                }
            }

            return $locked->participants()->create([
                'user_id' => $player->id,
                'entry_paid' => $fee,
            ]);
        });
    }

    public function leave(Tournament $tournament, User $user): void
    {
        $participant = $tournament->participants()->where('user_id', $user->id)->firstOrFail();

        $this->remove($tournament, $participant);
    }

    public function remove(Tournament $tournament, TournamentParticipant $participant): void
    {
        DB::transaction(function () use ($tournament, $participant) {
            $locked = Tournament::whereKey($tournament->id)->lockForUpdate()->first();

            if ($locked->status !== 'draft') {
                throw ValidationException::withMessages(['tournament' => 'Peserta hanya bisa keluar sebelum bagan dibuat.']);
            }

            if ($participant->entry_paid > 0) {
                $refund = $participant->entry_paid;
                $this->ledger->record($participant->user, $refund, 'tournament_refund', null, "Pengembalian biaya daftar turnamen {$locked->name}");

                if ($locked->creator?->isVendor()) {
                    $this->ledger->record($locked->creator, -$refund, 'tournament_entry_refunded', null, "Pengembalian poin {$participant->user->name} dari turnamen {$locked->name}");
                }
            }

            $participant->delete();
        });
    }
}
