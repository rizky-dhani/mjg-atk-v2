<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtkTransferStockItem extends Model
{
    protected $fillable = [
        'transfer_stock_id',
        'item_id', // Reference to the AtkItem model
        'item_category_id', // Added for item category
        'quantity',
        'notes',
    ];

    public function transferStock(): BelongsTo
    {
        return $this->belongsTo(AtkTransferStock::class, 'transfer_stock_id');
    }



    public function itemCategory(): BelongsTo
    {
        return $this->belongsTo(\App\Models\AtkCategory::class, 'item_category_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(AtkItem::class, 'item_id');
    }
}