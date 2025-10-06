<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtkDivisionStock extends Model
{
    protected $fillable = [
        'division_id',
        'item_id',
        'quantity',
        'max_stock_limit'
    ];

    public function division()
    {
        return $this->belongsTo(UserDivision::class, 'division_id');
    }

    public function item()
    {
        return $this->belongsTo(AtkItem::class, 'item_id');
    }
}
