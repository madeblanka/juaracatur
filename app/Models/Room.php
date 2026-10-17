<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'challenger_id',
        'opponent_id',
        'venue_id',
        'wager_points',
        'status',
        'scheduled_at',
        'notes',
        'challenger_reported_winner_id',
        'opponent_reported_winner_id',
        'winner_id',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'wager_points' => 'integer',
        ];
    }

    public function challenger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'challenger_id');
    }

    public function opponent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opponent_id');
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    public function pointTransactions(): HasMany
    {
        return $this->hasMany(PointTransaction::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(RoomMessage::class);
    }

    public function scopeInLobby(Builder $query): void
    {
        $query->whereIn('status', ['open', 'accepted']);
    }

    public function isOpenChallenge(): bool
    {
        return $this->status === 'open' && $this->opponent_id === null;
    }

    public function hasParticipant(int $userId): bool
    {
        return $this->challenger_id === $userId || $this->opponent_id === $userId;
    }
}
