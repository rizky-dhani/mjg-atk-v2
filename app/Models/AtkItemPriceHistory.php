<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtkItemPriceHistory extends Model
{
    protected $fillable = [
        'item_id',
        'old_price',
        'new_price',
        'effective_date',
        'changed_by',
    ];

    protected $casts = [
        'old_price' => 'integer',
        'new_price' => 'integer',
        'effective_date' => 'date',
        'changed_by' => 'integer',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(AtkItem::class, 'item_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}