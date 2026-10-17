<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Models\TournamentInvite;
use App\Models\TournamentMatch;
use App\Notifications\TournamentMatchReport;
use App\Services\TournamentEntry;
use App\Services\TournamentResults;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TournamentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Tournament::withCount('participants')->latest();

        $now = now();
        $status = $request->query('status');

        if ($status === 'upcoming') {
            $query->where('status', 'ongoing')->where('starts_at', '>', $now);
        } elseif ($status === 'ongoing') {
            $query->where('status', 'ongoing')->where('starts_at', '<=', $now);
        } elseif ($status === 'completed') {
            $query->where('status', 'completed');
        }

        return view('tournaments.index', [
            'tournaments' => $query->get(),
            'activeStatus' => $status,
        ]);
    }

    public function show(Tournament $tournament): View
    {
        $tournament->load([
            'participants.user',
            'matches.player1',
            'matches.player2',
            'matches.winner',
            'venue',
        ]);

        $standings = $tournament->participants
            ->sortByDesc(fn ($participant) => $participant->points())
            ->values();

        $myInvite = TournamentInvite::where('tournament_id', $tournament->id)
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->first();

        $myMatch = $tournament->matches->first(fn ($m) => $this->isPlayerInMatch($m, auth()->id()) && $m->status === 'pending' && $m->isReadyToPlay());
        $myDisputedMatch = $tournament->matches->first(fn ($m) => $this->isPlayerInMatch($m, auth()->id()) && $m->status === 'disputed');

        return view('tournaments.show', [
            'tournament' => $tournament,
            'standings' => $standings,
            'myInvite' => $myInvite,
            'myMatch' => $myMatch,
            'myDisputedMatch' => $myDisputedMatch,
        ]);
    }

    public function join(Request $request, Tournament $tournament, TournamentEntry $entry): RedirectResponse
    {
        $entry->join($tournament, $request->user());

        return back()->with('success', 'Kamu berhasil terdaftar di turnamen ini.');
    }

    public function leave(Request $request, Tournament $tournament, TournamentEntry $entry): RedirectResponse
    {
        $entry->leave($tournament, $request->user());

        return back()->with('success', 'Kamu keluar dari turnamen. Biaya pendaftaran sudah dikembalikan.');
    }

    public function reportMatch(Request $request, Tournament $tournament, TournamentMatch $match, TournamentResults $results): RedirectResponse
    {
        abort_unless($match->tournament_id === $tournament->id, 404);
        abort_unless($tournament->status === 'ongoing', 403, 'Turnamen ini tidak sedang berjalan.');

        $user = $request->user();
        abort_unless($this->isPlayerInMatch($match, $user->id), 403);
        abort_unless($match->status === 'pending' && $match->isReadyToPlay(), 403, 'Pertandingan ini sudah tercatat atau sedang sengketa.');

        $allowedWinners = $tournament->isElimination()
            ? [$match->player1_id, $match->player2_id]
            : [$match->player1_id, $match->player2_id, 0];

        $data = $request->validate([
            'winner_id' => ['required', 'integer', 'in:'.implode(',', $allowedWinners)],
        ]);
        $reported = (int) $data['winner_id'];

        $outcome = DB::transaction(function () use ($match, $user, $reported) {
            $locked = TournamentMatch::whereKey($match->id)->lockForUpdate()->first();
            abort_unless($locked->status === 'pending', 403, 'Pertandingan ini sudah tercatat atau sedang sengketa.');

            if ($user->id === $locked->player1_id) {
                $locked->update(['player1_reported_winner_id' => $reported]);
            } else {
                $locked->update(['player2_reported_winner_id' => $reported]);
            }

            $locked->refresh();

            if ($locked->player1_reported_winner_id === null || $locked->player2_reported_winner_id === null) {
                return 'waiting';
            }

            if ($locked->player1_reported_winner_id !== $locked->player2_reported_winner_id) {
                $locked->update(['status' => 'disputed']);

                return 'disputed';
            }

            return 'agreed';
        });

        $match->refresh();
        $opponent = $user->id === $match->player1_id ? $match->player2 : $match->player1;

        if ($outcome === 'waiting') {
            $opponent->notify(new TournamentMatchReport($tournament, $user->name.' sudah melaporkan hasil pertandingan di turnamen '.$tournament->name.'. Laporkan hasilmu untuk mengonfirmasi.'));

            return back()->with('success', 'Laporanmu tersimpan. Menunggu laporan dari lawan.');
        }

        if ($outcome === 'disputed') {
            $message = 'Laporan hasil berbeda di turnamen '.$tournament->name.'. Admin akan meninjau pertandingan ini.';
            $match->player1->notify(new TournamentMatchReport($tournament, $message));
            $match->player2->notify(new TournamentMatchReport($tournament, $message));

            return back()->with('error', 'Laporan hasil berbeda dengan lawan. Pertandingan ditandai sengketa dan akan ditinjau admin.');
        }

        $results->record($tournament, $match, $reported);

        return back()->with('success', 'Hasil pertandingan dikonfirmasi kedua pemain.');
    }

    private function isPlayerInMatch(TournamentMatch $match, ?int $userId): bool
    {
        return $userId !== null && in_array($userId, [$match->player1_id, $match->player2_id], true);
    }

    public function respondInvite(Request $request, Tournament $tournament, TournamentEntry $entry): RedirectResponse
    {
        $data = $request->validate([
            'decision' => ['required', 'in:accept,decline'],
        ]);

        $invite = TournamentInvite::where('tournament_id', $tournament->id)
            ->where('user_id', $request->user()->id)
            ->where('status', 'pending')
            ->firstOrFail();

        if ($data['decision'] === 'decline') {
            $invite->update(['status' => 'declined']);

            return back()->with('success', 'Undangan ditolak.');
        }

        abort_unless($tournament->status === 'draft', 403, 'Turnamen ini sudah dimulai, undangan tidak bisa diterima.');

        if (! $tournament->participants()->where('user_id', $request->user()->id)->exists()) {
            $entry->join($tournament, $request->user());
        }

        $invite->update(['status' => 'accepted']);

        return back()->with('success', 'Kamu sekarang terdaftar sebagai peserta turnamen ini.');
    }
}
