<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtkRequestFromFloatingStockItem extends Model
{
    protected $table = 'atk_requests_from_floating_stock_items';

    protected $fillable = [
        'request_id',
        'item_id',
        'quantity',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(AtkRequestFromFloatingStock::class, 'request_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(AtkItem::class, 'item_id');
    }
}