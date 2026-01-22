<?php

namespace App\Models;

class AtkFulfillment extends AtkStockRequest
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'atk_stock_requests';

    public function getMorphClass()
    {
        return AtkStockRequest::class;
    }
}
