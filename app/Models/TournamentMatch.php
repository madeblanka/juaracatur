<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TournamentMatch extends Model
{
    use HasFactory;

    protected $table = 'tournament_matches';

    protected $fillable = [
        'tournament_id',
        'player1_id',
        'player2_id',
        'winner_id',
        'player1_reported_winner_id',
        'player2_reported_winner_id',
        'next_match_id',
        'next_match_slot',
        'bracket',
        'loser_next_match_id',
        'loser_next_match_slot',
        'round',
        'scheduled_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
        ];
    }

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function player1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player1_id');
    }

    public function player2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player2_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    public function nextMatch(): BelongsTo
    {
        return $this->belongsTo(self::class, 'next_match_id');
    }

    public function loserNextMatch(): BelongsTo
    {
        return $this->belongsTo(self::class, 'loser_next_match_id');
    }

    public function isReadyToPlay(): bool
    {
        return $this->player1_id !== null && $this->player2_id !== null;
    }
}
