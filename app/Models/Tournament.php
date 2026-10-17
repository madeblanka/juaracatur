<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tournament extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'flyer',
        'format',
        'rr_rounds',
        'status',
        'venue_id',
        'created_by',
        'starts_at',
        'round_gap_minutes',
        'entry_points',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
        ];
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(TournamentParticipant::class);
    }

    public function invites(): HasMany
    {
        return $this->hasMany(TournamentInvite::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(TournamentMatch::class);
    }

    public function flyerUrl(): ?string
    {
        return $this->flyer ? asset('storage/'.$this->flyer) : null;
    }

    public function isSingleElimination(): bool
    {
        return $this->format === 'single_elimination';
    }

    public function isRoundRobin(): bool
    {
        return $this->format === 'round_robin';
    }

    public function roundRobinLabel(): string
    {
        return match ((int) $this->rr_rounds) {
            2 => 'Double Round Robin',
            3 => 'Triple Round Robin',
            4 => 'Quadruple Round Robin',
            5 => 'Quintuple Round Robin',
            default => 'Round Robin',
        };
    }

    public function formatLabel(): string
    {
        return match (true) {
            $this->isDoubleElimination() => 'Double Elimination (1 lower bracket)',
            $this->isSingleElimination() => 'Single Elimination',
            default => $this->roundRobinLabel(),
        };
    }

    public function isDoubleElimination(): bool
    {
        return $this->format === 'double_elimination';
    }

    public function isElimination(): bool
    {
        return $this->isSingleElimination() || $this->isDoubleElimination();
    }

    public function roundLabel(string $bracket, int $round, int $maxRound): string
    {
        if ($bracket === 'grand') {
            return 'Grand Final';
        }

        if ($bracket === 'losers') {
            $winnersRounds = (int) $this->matches()->where('bracket', 'winners')->max('round');

            return 'Lower R'.($round - $winnersRounds);
        }

        if ($this->isDoubleElimination()) {
            return $round === $maxRound ? 'Winners Final' : 'Winners R'.$round;
        }

        if (! $this->isSingleElimination()) {
            if ($this->isRoundRobin() && $this->rr_rounds > 1 && $maxRound > 0) {
                $baseRounds = intdiv($maxRound, $this->rr_rounds) ?: $maxRound;
                $cycle = intdiv($round - 1, $baseRounds) + 1;
                $localRound = (($round - 1) % $baseRounds) + 1;

                return "Putaran {$cycle} \u{00B7} Ronde {$localRound}";
            }

            return 'Ronde '.$round;
        }

        return match ($maxRound - $round) {
            0 => 'Final',
            1 => 'Semifinal',
            2 => 'Perempat Final',
            default => 'Ronde '.$round,
        };
    }
}
