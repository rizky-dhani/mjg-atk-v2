<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtkStockRequestItem extends Model
{
    protected $fillable = [
        'request_id',
        'item_id',
        'category_id',
        'quantity',
        'received_quantity',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => \App\Enums\AtkStockRequestItemStatus::class,
        ];
    }

    public function request()
    {
        return $this->belongsTo(AtkStockRequest::class, 'request_id');
    }

    public function item()
    {
        return $this->belongsTo(AtkItem::class, 'item_id');
    }

    public function category()
    {
        return $this->belongsTo(AtkCategory::class, 'category_id');
    }

    /**
     * Get remaining quantity to be received
     */
    public function getRemainingQuantityAttribute(): int
    {
        return max(0, $this->quantity - $this->received_quantity);
    }

    /**
     * Check if the item is fully received
     */
    public function isFullyReceived(): bool
    {
        return $this->received_quantity >= $this->quantity;
    }

    /**
     * Check if the item is partially received
     */
    public function isPartiallyReceived(): bool
    {
        return $this->received_quantity > 0 && $this->received_quantity < $this->quantity;
    }
}
