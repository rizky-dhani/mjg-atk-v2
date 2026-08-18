<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FulfillmentHistory extends Model
{
    protected $fillable = [
        'request_id',
        'item_id',
        'quantity',
        'notes',
        'user_id',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(AtkStockRequest::class, 'request_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(AtkItem::class, 'item_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
