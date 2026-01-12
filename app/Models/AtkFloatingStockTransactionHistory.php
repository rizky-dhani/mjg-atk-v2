<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AtkFloatingStockTransactionHistory extends Model
{
    protected $table = 'atk_floating_stock_trx';

    protected $fillable = [
        'item_id',
        'source_division_id',
        'destination_division_id',
        'type',
        'quantity',
        'unit_cost',
        'total_cost',
        'mac_snapshot',
        'balance_snapshot',
        'trx_src_id',
        'trx_src_type',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(AtkItem::class, 'item_id');
    }

    public function sourceDivision(): BelongsTo
    {
        return $this->belongsTo(UserDivision::class, 'source_division_id');
    }

    public function destinationDivision(): BelongsTo
    {
        return $this->belongsTo(UserDivision::class, 'destination_division_id');
    }

    public function trx_src(): MorphTo
    {
        return $this->morphTo();
    }
}