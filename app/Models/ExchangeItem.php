<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExchangeItem extends Model
{
    protected $fillable = [
        'vendor_id',
        'type',
        'name',
        'description',
        'point_cost',
        'stock',
        'expires_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'point_cost' => 'integer',
            'stock' => 'integer',
            'expires_at' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    /** @return HasMany<Redemption, $this> */
    public function redemptions(): HasMany
    {
        return $this->hasMany(Redemption::class);
    }

    public function isVoucher(): bool
    {
        return $this->type === 'voucher';
    }

    public function isAvailable(): bool
    {
        return $this->is_active
            && ($this->stock === null || $this->stock > 0)
            && ($this->expires_at === null || $this->expires_at->isToday() || $this->expires_at->isFuture());
    }

    public function scopeAvailable(Builder $query): void
    {
        $query->where('is_active', true)
            ->where(fn (Builder $q) => $q->whereNull('stock')->orWhere('stock', '>', 0))
            ->where(fn (Builder $q) => $q->whereNull('expires_at')->orWhereDate('expires_at', '>=', now()->toDateString()))
            ->where(function (Builder $q) {
                $q->where('type', 'voucher')->orWhereHas('vendor', fn (Builder $v) => $v->where('role', 'vendor')->where('is_active', true));
            });
    }
}
