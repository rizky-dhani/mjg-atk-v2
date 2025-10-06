<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtkStockRequest extends Model
{
    protected $fillable = [
        'request_number',
        'requester_id',
        'division_id',
        'notes'
    ];

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function division()
    {
        return $this->belongsTo(UserDivision::class, 'division_id');
    }

    public function atkStockRequestItems()
    {
        return $this->hasMany(AtkStockRequestItem::class, 'request_id');
    }

    public function approval()
    {
        return $this->morphOne(Approval::class, 'approvable');
    }
}
