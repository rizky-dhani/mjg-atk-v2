<?php

namespace App\Models;

use App\Enums\AtkStockRequestItemStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
            'status' => AtkStockRequestItemStatus::class,
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
     * Get the requester division's stock for this item
     */
    public function divisionStock(): HasOne
    {
        return $this->hasOne(AtkDivisionStock::class, 'item_id', 'item_id')
            ->where('division_id', fn ($q) => $q->select('division_id')->from('atk_stock_requests')->where('id', $this->request_id));
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

    /**
     * Get the requester division's current stock for this item
     */
    public function getDivisionCurrentStock(): int
    {
        return $this->divisionStock?->current_stock ?? 0;
    }
}
