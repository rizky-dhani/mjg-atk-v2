<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtkStockRequestItem extends Model
{
    protected $fillable = [
        'request_id',
        'item_id',
        'category_id',
        'quantity_requested'
    ];

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
}
