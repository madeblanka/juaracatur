<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Redemption extends Model
{
    protected $fillable = [
        'user_id',
        'exchange_item_id',
        'vendor_id',
        'item_name',
        'point_cost',
        'status',
        'voucher_code',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'point_cost' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(ExchangeItem::class, 'exchange_item_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }
}
