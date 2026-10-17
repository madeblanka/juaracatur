<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'points',
        'wilayah',
        'phone',
        'photo',
        'age',
        'birthdate',
        'kode_wilayah',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'points' => 'integer',
            'birthdate' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isVendor(): bool
    {
        return $this->role === 'vendor';
    }

    public function isVenue(): bool
    {
        return $this->role === 'venue';
    }

    /** @return HasOne<Venue, $this> */
    public function venue(): HasOne
    {
        return $this->hasOne(Venue::class, 'owner_id');
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /** @return HasMany<ExchangeItem, $this> */
    public function exchangeItems(): HasMany
    {
        return $this->hasMany(ExchangeItem::class, 'vendor_id');
    }

    /** @return HasMany<Redemption, $this> */
    public function redemptions(): HasMany
    {
        return $this->hasMany(Redemption::class);
    }

    public function hasHistory(): bool
    {
        return Room::where('challenger_id', $this->id)->orWhere('opponent_id', $this->id)->exists()
            || $this->pointTransactions()->exists()
            || $this->redemptions()->exists()
            || $this->exchangeItems()->exists()
            || TournamentParticipant::where('user_id', $this->id)->exists();
    }

    /** @return HasMany<Room, $this> */
    public function challengedRooms(): HasMany
    {
        return $this->hasMany(Room::class, 'challenger_id');
    }

    /** @return HasMany<Room, $this> */
    public function joinedRooms(): HasMany
    {
        return $this->hasMany(Room::class, 'opponent_id');
    }

    /** @return HasMany<PointTransaction, $this> */
    public function pointTransactions(): HasMany
    {
        return $this->hasMany(PointTransaction::class);
    }

    /** @return HasMany<Room, $this> */
    public function wonRooms(): HasMany
    {
        return $this->hasMany(Room::class, 'winner_id');
    }

    public function completedRoomsCount(): int
    {
        return Room::query()
            ->where('status', 'completed')
            ->where(fn ($q) => $q->where('challenger_id', $this->id)->orWhere('opponent_id', $this->id))
            ->count();
    }

    public function sendPasswordResetNotification(#[SensitiveParameter] $token): void
    {
        $this->notify(new \App\Notifications\ResetPlayerPassword($token));
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new \App\Notifications\VerifyPlayerEmail);
    }

    public function whatsappUrl(): ?string
    {
        if (! $this->phone) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $this->phone);

        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        } elseif (! str_starts_with($digits, '62')) {
            $digits = '62'.$digits;
        }

        return "https://wa.me/{$digits}";
    }

    public function photoUrl(): ?string
    {
        return $this->photo ? asset('storage/'.$this->photo) : null;
    }

    public function usia(): ?int
    {
        return $this->birthdate ? $this->birthdate->age : $this->age;
    }

    public function domisiliLabel(): ?string
    {
        return $this->kode_wilayah ? Wilayah::labelDomisili($this->kode_wilayah) : null;
    }
}
