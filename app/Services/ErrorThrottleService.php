<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Throwable;

class ErrorThrottleService
{
    protected int $throttleMinutes = 5;

    public function shouldSend(Throwable $exception, Request $request): bool
    {
        $key = $this->getKey($exception, $request);

        if (Cache::has($key)) {
            return false;
        }

        Cache::put($key, true, now()->addMinutes($this->throttleMinutes));

        return true;
    }

    protected function getKey(Throwable $exception, Request $request): string
    {
        return 'error_throttle:'.get_class($exception).':'.$request->url();
    }
}
