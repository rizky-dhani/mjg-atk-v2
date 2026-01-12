<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtkStockTransaction extends Model
{
    protected $table = 'atk_stock_trx';

    protected $fillable = [
        'division_id',
        'item_id',
        'type',
        'quantity',
        'unit_cost',
        'total_cost',
        'mac_snapshot',
        'balance_snapshot',
        'trx_src_type',
        'trx_src_id',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_cost' => 'integer',
        'total_cost' => 'integer',
        'mac_snapshot' => 'integer',
        'balance_snapshot' => 'integer',
    ];

    public function division(): BelongsTo
    {
        return $this->belongsTo(UserDivision::class, 'division_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(AtkItem::class, 'item_id');
    }

    /**
     * Get the source model that initiated the transaction (polymorphic)
     */
    public function transactionSource()
    {
        return $this->morphTo('trx_src');
    }

    /**
     * Scope to get transactions for a specific division and item
     */
    public function scopeForDivisionAndItem(Builder $query, int $divisionId, int $itemId): Builder
    {
        return $query->where('division_id', $divisionId)
            ->where('item_id', $itemId);
    }
}
