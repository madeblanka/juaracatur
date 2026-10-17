<?php

namespace App\Services;

use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Notifications\TournamentMatchResult;
use Illuminate\Support\Facades\DB;

class TournamentResults
{
    public function record(Tournament $tournament, TournamentMatch $match, int $winnerId): void
    {
        DB::transaction(function () use ($tournament, $match, $winnerId) {
            $isDraw = $winnerId === 0;

            if ($match->status === 'completed') {
                $this->revertStanding($tournament, $match);
            }

            $match->update([
                'winner_id' => $isDraw ? null : $winnerId,
                'status' => 'completed',
                'player1_reported_winner_id' => null,
                'player2_reported_winner_id' => null,
            ]);

            if ($isDraw) {
                $tournament->participants()->where('user_id', $match->player1_id)->increment('draws');
                $tournament->participants()->where('user_id', $match->player2_id)->increment('draws');

                return;
            }

            $loserId = $winnerId === $match->player1_id ? $match->player2_id : $match->player1_id;
            $tournament->participants()->where('user_id', $winnerId)->increment('wins');
            $tournament->participants()->where('user_id', $loserId)->increment('losses');

            if ($tournament->isElimination() && $match->next_match_id) {
                $slotField = $match->next_match_slot === 1 ? 'player1_id' : 'player2_id';
                TournamentMatch::whereKey($match->next_match_id)->update([$slotField => $winnerId]);
            }

            if ($tournament->isDoubleElimination() && $match->loser_next_match_id) {
                $slotField = $match->loser_next_match_slot === 1 ? 'player1_id' : 'player2_id';
                TournamentMatch::whereKey($match->loser_next_match_id)->update([$slotField => $loserId]);
            }
        });

        if ($tournament->matches()->whereIn('status', ['pending', 'disputed'])->count() === 0) {
            $tournament->update(['status' => 'completed']);
        }

        $match->refresh();
        $match->player1->notify(new TournamentMatchResult($match));
        $match->player2->notify(new TournamentMatchResult($match));
    }

    private function revertStanding(Tournament $tournament, TournamentMatch $match): void
    {
        if ($match->winner_id === null) {
            $tournament->participants()->where('user_id', $match->player1_id)->decrement('draws');
            $tournament->participants()->where('user_id', $match->player2_id)->decrement('draws');

            return;
        }

        $loserId = $match->winner_id === $match->player1_id ? $match->player2_id : $match->player1_id;
        $tournament->participants()->where('user_id', $match->winner_id)->decrement('wins');
        $tournament->participants()->where('user_id', $loserId)->decrement('losses');
    }
}
