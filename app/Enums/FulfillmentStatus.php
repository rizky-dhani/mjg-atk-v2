<?php

namespace App\Enums;

enum FulfillmentStatus: string
{
    case Pending = 'pending';
    case PartiallyFulfilled = 'partially_fulfilled';
    case Fulfilled = 'fulfilled';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu',
            self::PartiallyFulfilled => 'Terpenuhi Sebagian',
            self::Fulfilled => 'Terpenuhi',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'gray',
            self::PartiallyFulfilled => 'info',
            self::Fulfilled => 'success',
        };
    }
}
