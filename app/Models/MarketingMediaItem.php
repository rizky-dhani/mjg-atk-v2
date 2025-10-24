<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketingMediaItem extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'unit_of_measure',
        'category_id',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(MarketingMediaCategory::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(MarketingMediaDivisionStock::class, 'item_id');
    }
}