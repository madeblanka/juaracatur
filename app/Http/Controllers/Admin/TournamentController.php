<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Models\User;
use App\Models\Venue;
use App\Models\TournamentInvite;
use App\Notifications\TournamentInvited;
use App\Notifications\TournamentScheduled;
use App\Services\TournamentEntry;
use App\Services\TournamentResults;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TournamentController extends Controller
{
    /**
     * The first segment of the current route name ("admin" or "vendor"), so the
     * same controller and views work for both /admin/tournaments and
     * /vendor/turnamen without hardcoding which one is active.
     */
    protected function routePrefix(): string
    {
        // Falls back to "admin" when there's no routed HTTP request bound (e.g. a
        // script invoking generate()/destroyBagan() directly, as the production
        // demo-tournament seeder does) - matches the previous hardcoded behavior.
        $routeName = optional(request()->route())->getName();

        return $routeName ? explode('.', $routeName)[0] : 'admin';
    }

    public function index(): View
    {
        $tournaments = Tournament::withCount('participants')->latest()->get();

        return view('admin.tournaments.index', ['tournaments' => $tournaments]);
    }

    public function create(): View
    {
        $venues = Venue::where('is_active', true)->orderBy('name')->get();

        return view('admin.tournaments.create', ['venues' => $venues]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'venue_id' => ['nullable', 'exists:venues,id'],
            'format' => ['required', 'in:round_robin,single_elimination,double_elimination'],
            'rr_rounds' => ['nullable', 'integer', 'between:1,5'],
            'flyer' => ['nullable', 'image', 'max:2048'],
            'entry_points' => ['required', 'integer', 'min:0', 'max:100000'],
        ], [
            'flyer.image' => 'File flyer harus berupa gambar (jpg, png, atau webp).',
            'flyer.max' => 'Ukuran flyer maksimal 2MB.',
            'rr_rounds.between' => 'Jumlah putaran round robin harus antara 1 sampai 5.',
        ]);

        $tournament = Tournament::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'flyer' => $request->hasFile('flyer') ? $request->file('flyer')->store('tournaments', 'public') : null,
            'venue_id' => $data['venue_id'] ?? null,
            'format' => $data['format'],
            'rr_rounds' => $data['format'] === 'round_robin' ? ($data['rr_rounds'] ?? 1) : 1,
            'entry_points' => $data['entry_points'],
            'status' => 'draft',
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route($this->routePrefix().'.tournaments.show', $tournament)->with('success', 'Turnamen dibuat. Tambahkan peserta lalu buat bagan.');
    }

    public function show(Tournament $tournament): View
    {
        $tournament->load(['participants.user', 'matches.player1', 'matches.player2', 'matches.winner', 'venue']);

        $players = User::where('role', 'player')->active()
            ->whereNotIn('id', $tournament->participants->pluck('user_id'))
            ->orderBy('name')
            ->get();

        $standings = $tournament->participants->sortByDesc(fn ($p) => $p->points())->values();

        return view('admin.tournaments.show', [
            'tournament' => $tournament,
            'players' => $players,
            'invitedIds' => $tournament->invites()->where('status', 'pending')->pluck('user_id')->all(),
            'venues' => Venue::where('is_active', true)->orderBy('name')->get(),
            'standings' => $standings,
        ]);
    }

    public function updateBagan(Request $request, Tournament $tournament): RedirectResponse
    {
        abort_unless($tournament->status === 'ongoing', 403, 'Jadwal hanya bisa diubah saat turnamen berjalan.');
        abort_if($tournament->matches()->where('status', 'completed')->exists(), 403, 'Jadwal tidak bisa diubah karena sudah ada hasil pertandingan.');

        $data = $request->validate([
            'starts_at' => ['required', 'date', 'after:now'],
            'round_gap_minutes' => ['required', 'integer', 'min:30', 'max:1440'],
        ]);

        $startsAt = Carbon::parse($data['starts_at']);
        $gapMinutes = (int) $data['round_gap_minutes'];

        DB::transaction(function () use ($tournament, $startsAt, $gapMinutes) {
            foreach ($tournament->matches as $match) {
                $match->update([
                    'scheduled_at' => $startsAt->copy()->addMinutes(($match->round - 1) * $gapMinutes),
                ]);
            }

            $tournament->update([
                'starts_at' => $startsAt,
                'round_gap_minutes' => $gapMinutes,
            ]);
        });

        foreach (User::whereIn('id', $tournament->participants()->pluck('user_id'))->get() as $participant) {
            $participant->notify(new TournamentScheduled($tournament));
        }

        return back()->with('success', 'Jadwal bagan diperbarui. Peserta sudah diberi tahu.');
    }

    public function destroyBagan(Tournament $tournament): RedirectResponse
    {
        abort_unless($tournament->status === 'ongoing', 403, 'Bagan hanya bisa dihapus saat turnamen berjalan.');
        abort_if($tournament->matches()->where('status', 'completed')->exists(), 403, 'Bagan tidak bisa dihapus karena sudah ada hasil pertandingan.');

        DB::transaction(function () use ($tournament) {
            $tournament->matches()->update([
                'next_match_id' => null,
                'next_match_slot' => null,
                'loser_next_match_id' => null,
                'loser_next_match_slot' => null,
            ]);
            $tournament->matches()->delete();

            $tournament->update([
                'status' => 'draft',
                'starts_at' => null,
                'round_gap_minutes' => null,
            ]);
        });

        return redirect()->route($this->routePrefix().'.tournaments.show', $tournament)->with('success', 'Bagan dihapus. Kamu bisa membuat bagan baru.');
    }

    public function updateVenue(Request $request, Tournament $tournament): RedirectResponse
    {
        $data = $request->validate([
            'venue_id' => ['nullable', Rule::exists('venues', 'id')->where('is_active', true)],
        ], [
            'venue_id.exists' => 'Pilih tempat yang aktif.',
        ]);

        $tournament->update(['venue_id' => $data['venue_id'] ?? null]);

        return back()->with('success', 'Lokasi turnamen diperbarui.');
    }

    public function updateEntryPoints(Request $request, Tournament $tournament): RedirectResponse
    {
        abort_unless($tournament->status === 'draft', 403, 'Biaya pendaftaran hanya bisa diubah sebelum bagan dibuat.');

        $data = $request->validate([
            'entry_points' => ['required', 'integer', 'min:0', 'max:100000'],
        ]);

        $tournament->update(['entry_points' => $data['entry_points']]);

        return back()->with('success', 'Biaya pendaftaran diperbarui.');
    }

    public function updateDescription(Request $request, Tournament $tournament): RedirectResponse
    {
        $data = $request->validate([
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $tournament->update(['description' => $data['description'] ?? null]);

        return back()->with('success', 'Deskripsi turnamen diperbarui.');
    }

    public function updateFlyer(Request $request, Tournament $tournament): RedirectResponse
    {
        $request->validate([
            'flyer' => ['required', 'image', 'max:2048'],
        ], [
            'flyer.required' => 'Pilih file flyer terlebih dahulu.',
            'flyer.image' => 'File flyer harus berupa gambar (jpg, png, atau webp).',
            'flyer.max' => 'Ukuran flyer maksimal 2MB.',
        ]);

        if ($tournament->flyer) {
            Storage::disk('public')->delete($tournament->flyer);
        }

        $tournament->update(['flyer' => $request->file('flyer')->store('tournaments', 'public')]);

        return back()->with('success', 'Flyer turnamen diperbarui.');
    }

    public function invite(Request $request, Tournament $tournament): RedirectResponse
    {
        abort_unless($tournament->status === 'draft', 403, 'Undangan hanya bisa dikirim sebelum bagan dibuat.');

        $data = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ], [
            'user_ids.required' => 'Pilih minimal satu pemain untuk diundang.',
        ]);

        $participantIds = $tournament->participants()->pluck('user_id');

        $players = User::where('role', 'player')->active()
            ->whereIn('id', $data['user_ids'])
            ->whereNotIn('id', $participantIds)
            ->get();

        foreach ($players as $player) {
            TournamentInvite::updateOrCreate(
                ['tournament_id' => $tournament->id, 'user_id' => $player->id],
                ['status' => 'pending'],
            );

            $player->notify(new TournamentInvited($tournament));
        }

        return back()->with('success', $players->count().' pemain diundang.');
    }

    public function addParticipant(Request $request, Tournament $tournament): RedirectResponse
    {
        abort_unless($tournament->status === 'draft', 403, 'Peserta hanya bisa ditambahkan sebelum bagan dibuat.');

        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $tournament->participants()->firstOrCreate(['user_id' => $data['user_id']]);

        return back()->with('success', 'Peserta ditambahkan.');
    }

    public function removeParticipant(Tournament $tournament, User $user, TournamentEntry $entry): RedirectResponse
    {
        abort_unless($tournament->status === 'draft', 403, 'Peserta hanya bisa diubah sebelum bagan dibuat.');

        $entry->remove($tournament, $tournament->participants()->where('user_id', $user->id)->firstOrFail());

        return back()->with('success', 'Peserta dihapus.');
    }

    public function generate(Request $request, Tournament $tournament): RedirectResponse
    {
        abort_unless($tournament->status === 'draft', 403, 'Jadwal turnamen sudah pernah dibuat.');

        $playerIds = $tournament->participants()->pluck('user_id')->values()->all();
        $minParticipants = match (true) {
            $tournament->isDoubleElimination() => 4,
            $tournament->isSingleElimination() => 2,
            default => 3,
        };

        if (count($playerIds) < $minParticipants) {
            return back()->with('error', "Minimal {$minParticipants} peserta untuk membuat jadwal turnamen ini.");
        }

        if ($tournament->isDoubleElimination() && (count($playerIds) & (count($playerIds) - 1)) !== 0) {
            return back()->with('error', 'Bracket dengan lower bracket membutuhkan jumlah peserta 4, 8, 16, atau 32. Tambah atau kurangi peserta dulu.');
        }

        $data = $request->validate([
            'starts_at' => ['required', 'date', 'after:now'],
            'round_gap_minutes' => ['required', 'integer', 'min:30', 'max:1440'],
        ]);

        $startsAt = Carbon::parse($data['starts_at']);
        $gapMinutes = (int) $data['round_gap_minutes'];

        DB::transaction(function () use ($tournament, $playerIds, $startsAt, $gapMinutes) {
            if ($tournament->isDoubleElimination()) {
                $this->buildDoubleEliminationBracket($tournament, $playerIds, $startsAt, $gapMinutes);
            } elseif ($tournament->isSingleElimination()) {
                $this->buildSingleEliminationBracket($tournament, $playerIds, $startsAt, $gapMinutes);
            } else {
                $pairings = $this->buildRoundRobinPairings($playerIds, (int) $tournament->rr_rounds);

                foreach ($pairings as $pairing) {
                    TournamentMatch::create([
                        'tournament_id' => $tournament->id,
                        'round' => $pairing['round'],
                        'player1_id' => $pairing['player1_id'],
                        'player2_id' => $pairing['player2_id'],
                        'scheduled_at' => $startsAt->copy()->addMinutes(($pairing['round'] - 1) * $gapMinutes),
                        'status' => 'pending',
                    ]);
                }
            }

            $tournament->update([
                'status' => 'ongoing',
                'starts_at' => $startsAt,
                'round_gap_minutes' => $gapMinutes,
            ]);
        });

        $participants = User::whereIn('id', $playerIds)->get();
        foreach ($participants as $participant) {
            $participant->notify(new TournamentScheduled($tournament));
        }

        return redirect()->route($this->routePrefix().'.tournaments.show', $tournament)->with('success', 'Jadwal turnamen berhasil dibuat. Setiap ronde sudah punya jadwal otomatis.');
    }

    public function setMatchResult(Request $request, Tournament $tournament, TournamentMatch $match, TournamentResults $results): RedirectResponse
    {
        abort_unless($match->tournament_id === $tournament->id, 404);
        abort_unless(in_array($tournament->status, ['ongoing', 'completed'], true), 403);
        abort_unless($match->isReadyToPlay(), 403, 'Pertandingan ini belum bisa dimulai, menunggu pemenang ronde sebelumnya.');

        if ($match->status === 'completed' && $match->next_match_id && $match->nextMatch?->status === 'completed') {
            return back()->with('error', 'Hasil ini tidak bisa diubah karena pemenangnya sudah bertanding di babak berikutnya.');
        }

        if ($match->status === 'completed' && $match->loser_next_match_id && $match->loserNextMatch?->status === 'completed') {
            return back()->with('error', 'Hasil ini tidak bisa diubah karena pecundangnya sudah bertanding di lower bracket.');
        }

        $allowedWinners = $tournament->isElimination()
            ? [$match->player1_id, $match->player2_id]
            : [$match->player1_id, $match->player2_id, 0];

        $data = $request->validate([
            'winner_id' => ['required', 'integer', 'in:'.implode(',', $allowedWinners)],
        ]);

        $results->record($tournament, $match, (int) $data['winner_id']);

        return back()->with('success', 'Hasil pertandingan dicatat.');
    }

    /**
     * Builds a single-elimination bracket: creates every match for every round up
     * front (later rounds start with null player slots), wiring each match's
     * next_match_id/next_match_slot so a winner automatically fills the correct
     * slot in the next round once set. Byes (when the participant count isn't a
     * power of 2) are resolved immediately — the bye'd player advances straight
     * into their next-round slot without a round-1 match being created for them.
     *
     * @param  list<int>  $playerIds
     */
    private function buildDoubleEliminationBracket(Tournament $tournament, array $playerIds, Carbon $startsAt, int $gapMinutes): void
    {
        $players = $playerIds;
        shuffle($players);

        $n = count($players);
        $rounds = (int) log($n, 2);
        $lowerRounds = 2 * ($rounds - 1);
        $grandRound = $rounds + $lowerRounds + 1;

        $at = fn (int $round) => $startsAt->copy()->addMinutes(($round - 1) * $gapMinutes);
        $create = fn (array $attrs) => TournamentMatch::create(array_merge([
            'tournament_id' => $tournament->id,
            'status' => 'pending',
        ], $attrs));

        $grand = $create(['bracket' => 'grand', 'round' => $grandRound, 'scheduled_at' => $at($grandRound)]);

        $lower = [];
        for ($lr = $lowerRounds; $lr >= 1; $lr--) {
            $count = $lr % 2 === 1 ? $n / 2 ** (($lr + 1) / 2 + 1) : $n / 2 ** ($lr / 2 + 1);
            $globalRound = $rounds + $lr;

            for ($j = 0; $j < $count; $j++) {
                if ($lr === $lowerRounds) {
                    $next = ['next_match_id' => $grand->id, 'next_match_slot' => 2];
                } elseif ($lr % 2 === 1) {
                    $next = ['next_match_id' => $lower[$lr + 1][$j]->id, 'next_match_slot' => 1];
                } else {
                    $next = ['next_match_id' => $lower[$lr + 1][intdiv($j, 2)]->id, 'next_match_slot' => $j % 2 === 0 ? 1 : 2];
                }

                $lower[$lr][$j] = $create(array_merge([
                    'bracket' => 'losers',
                    'round' => $globalRound,
                    'scheduled_at' => $at($globalRound),
                ], $next));
            }
        }

        $winners = [];
        for ($r = $rounds; $r >= 1; $r--) {
            $count = $n / 2 ** $r;

            for ($i = 0; $i < $count; $i++) {
                $attrs = ['bracket' => 'winners', 'round' => $r, 'scheduled_at' => $at($r)];

                if ($r === $rounds) {
                    $attrs['next_match_id'] = $grand->id;
                    $attrs['next_match_slot'] = 1;
                } else {
                    $attrs['next_match_id'] = $winners[$r + 1][intdiv($i, 2)]->id;
                    $attrs['next_match_slot'] = $i % 2 === 0 ? 1 : 2;
                }

                if ($r === 1) {
                    $attrs['player1_id'] = $players[2 * $i];
                    $attrs['player2_id'] = $players[2 * $i + 1];
                    $attrs['loser_next_match_id'] = $lower[1][intdiv($i, 2)]->id;
                    $attrs['loser_next_match_slot'] = $i % 2 === 0 ? 1 : 2;
                } else {
                    $attrs['loser_next_match_id'] = $lower[2 * ($r - 1)][$i]->id;
                    $attrs['loser_next_match_slot'] = 2;
                }

                $winners[$r][$i] = $create($attrs);
            }
        }
    }

    private function buildSingleEliminationBracket(Tournament $tournament, array $playerIds, Carbon $startsAt, int $gapMinutes): void
    {
        $players = $playerIds;
        shuffle($players);

        $bracketSize = 1;
        while ($bracketSize < count($players)) {
            $bracketSize *= 2;
        }

        $half = intdiv($bracketSize, 2);
        $byesNeeded = $bracketSize - count($players);

        // Spread byes across distinct pairs (never two byes in the same pair —
        // byesNeeded is always < $half, so this is always possible) rather than
        // padding+shuffling a flat slot array, which could randomly land two
        // byes in the same pair and produce a match with no real player on
        // either side, permanently stalling that branch of the bracket.
        $pairIndices = range(0, $half - 1);
        shuffle($pairIndices);
        $byePairs = array_flip(array_slice($pairIndices, 0, $byesNeeded));

        $pairs = [];
        $cursor = 0;
        for ($i = 0; $i < $half; $i++) {
            if (isset($byePairs[$i])) {
                $pairs[$i] = [$players[$cursor], null];
                $cursor++;
            } else {
                $pairs[$i] = [$players[$cursor], $players[$cursor + 1]];
                $cursor += 2;
            }
        }

        $totalRounds = (int) log($bracketSize, 2);

        // Round 1: pair up slots. A pair with one empty slot is a bye — the real
        // player advances immediately (represented here as a plain int). A pair
        // with two real players becomes a match (represented as the Eloquent model).
        $previousRound = [];
        for ($i = 0; $i < $half; $i++) {
            [$p1, $p2] = $pairs[$i];

            if ($p1 !== null && $p2 !== null) {
                $previousRound[] = TournamentMatch::create([
                    'tournament_id' => $tournament->id,
                    'round' => 1,
                    'player1_id' => $p1,
                    'player2_id' => $p2,
                    'scheduled_at' => $startsAt->copy(),
                    'status' => 'pending',
                ]);
            } else {
                $previousRound[] = $p1 ?? $p2;
            }
        }

        for ($round = 2; $round <= $totalRounds; $round++) {
            $currentRound = [];
            $matchesInRound = $bracketSize / (2 ** $round);

            for ($i = 0; $i < $matchesInRound; $i++) {
                $left = $previousRound[$i * 2];
                $right = $previousRound[$i * 2 + 1];

                $match = TournamentMatch::create([
                    'tournament_id' => $tournament->id,
                    'round' => $round,
                    'player1_id' => is_int($left) ? $left : null,
                    'player2_id' => is_int($right) ? $right : null,
                    'scheduled_at' => $startsAt->copy()->addMinutes(($round - 1) * $gapMinutes),
                    'status' => 'pending',
                ]);

                if ($left instanceof TournamentMatch) {
                    $left->update(['next_match_id' => $match->id, 'next_match_slot' => 1]);
                }
                if ($right instanceof TournamentMatch) {
                    $right->update(['next_match_id' => $match->id, 'next_match_slot' => 2]);
                }

                $currentRound[] = $match;
            }

            $previousRound = $currentRound;
        }
    }

    /**
     * Circle-method round robin scheduler. Returns list of ['round','player1_id','player2_id'].
     *
     * @param  list<int>  $playerIds
     * @return list<array{round:int,player1_id:int,player2_id:int}>
     */
    private function buildRoundRobinPairings(array $playerIds, int $times = 1): array
    {
        $players = $playerIds;

        if (count($players) % 2 !== 0) {
            $players[] = null;
        }

        $count = count($players);
        $baseRounds = $count - 1;
        $half = intdiv($count, 2);
        $singleCycle = [];

        for ($round = 0; $round < $baseRounds; $round++) {
            for ($i = 0; $i < $half; $i++) {
                $home = $players[$i];
                $away = $players[$count - 1 - $i];

                if ($home !== null && $away !== null) {
                    $singleCycle[] = [
                        'round' => $round + 1,
                        'player1_id' => $home,
                        'player2_id' => $away,
                    ];
                }
            }

            $fixed = $players[0];
            $rest = array_slice($players, 1);
            array_unshift($rest, array_pop($rest));
            $players = array_merge([$fixed], $rest);
        }

        $pairings = [];

        for ($cycle = 1; $cycle <= $times; $cycle++) {
            $swap = $cycle % 2 === 0;

            foreach ($singleCycle as $pairing) {
                $pairings[] = [
                    'round' => $pairing['round'] + ($cycle - 1) * $baseRounds,
                    'player1_id' => $swap ? $pairing['player2_id'] : $pairing['player1_id'],
                    'player2_id' => $swap ? $pairing['player1_id'] : $pairing['player2_id'],
                ];
            }
        }

        return $pairings;
    }
}
