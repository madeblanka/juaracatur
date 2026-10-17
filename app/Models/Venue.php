<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class Venue extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'name',
        'address',
        'wilayah',
        'phone',
        'description',
        'latitude',
        'longitude',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** @return HasMany<Room, $this> */
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    /** @return HasMany<Tournament, $this> */
    public function tournaments(): HasMany
    {
        return $this->hasMany(Tournament::class);
    }

    /** @return HasMany<VenuePhoto, $this> */
    public function photos(): HasMany
    {
        return $this->hasMany(VenuePhoto::class);
    }

    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    public function mapQuery(): string
    {
        return $this->hasCoordinates()
            ? "{$this->latitude},{$this->longitude}"
            : trim(($this->address ?? '').' '.($this->wilayah ?? ''));
    }

    public function mapEmbedUrl(): string
    {
        return 'https://maps.google.com/maps?q='.rawurlencode($this->mapQuery()).'&z=16&output=embed';
    }

    public function mapLink(): string
    {
        return 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($this->mapQuery());
    }

    public static function parseLokasiGoogleMaps(string $input): array
    {
        $number = '(-?\d+(?:\.\d+)?)';
        $patterns = [
            "/^\s*{$number}\s*,\s*{$number}\s*$/",
            "/@{$number},{$number}/",
            "/!3d{$number}!4d{$number}/",
            "/[?&]q={$number},{$number}/",
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input, $m) && abs((float) $m[1]) <= 90 && abs((float) $m[2]) <= 180) {
                return [(float) $m[1], (float) $m[2]];
            }
        }

        throw ValidationException::withMessages([
            'lokasi_google_maps' => 'Lokasi tidak terbaca. Klik kanan pada pin di Google Maps lalu salin angka koordinatnya (contoh: -8.670458, 115.212629), atau tempel link Google Maps lengkap.',
        ]);
    }
}
