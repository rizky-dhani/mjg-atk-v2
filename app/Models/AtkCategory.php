<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtkCategory extends Model
{
    protected $fillable = [
        'name',
        'description'
    ];

    public function atkItems()
    {
        return $this->hasMany(AtkItem::class, 'category_id');
    }
}
