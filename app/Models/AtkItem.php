<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtkItem extends Model
{
    protected $fillable = [
        'name',
        'description',
        'category_id',
        'unit'
    ];

    public function category()
    {
        return $this->belongsTo(AtkCategory::class, 'category_id');
    }

    public function atkDivisionStocks()
    {
        return $this->hasMany(AtkDivisionStock::class, 'item_id');
    }

    public function atkStockRequestItems()
    {
        return $this->hasMany(AtkStockRequestItem::class, 'item_id');
    }

    public function atkStockUsageItems()
    {
        return $this->hasMany(AtkStockUsageItem::class, 'item_id');
    }
}
