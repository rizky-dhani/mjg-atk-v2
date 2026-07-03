<?php

use App\Services\ErrorThrottleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

uses(TestCase::class);

it('returns true for first error', function () {
    Cache::flush();
    $service = new ErrorThrottleService;
    $exception = new NotFoundHttpException;
    $request = Request::create('/test');

    expect($service->shouldSend($exception, $request))->toBeTrue();
});

it('returns false for duplicate error within 5 minutes', function () {
    Cache::flush();
    $service = new ErrorThrottleService;
    $exception = new NotFoundHttpException;
    $request = Request::create('/test');

    $service->shouldSend($exception, $request);
    expect($service->shouldSend($exception, $request))->toBeFalse();
});

it('returns true for different URL', function () {
    Cache::flush();
    $service = new ErrorThrottleService;
    $exception = new NotFoundHttpException;

    $service->shouldSend($exception, Request::create('/test'));
    expect($service->shouldSend($exception, Request::create('/other')))->toBeTrue();
});

it('returns true for different exception class', function () {
    Cache::flush();
    $service = new ErrorThrottleService;
    $request = Request::create('/test');

    $service->shouldSend(new NotFoundHttpException, $request);
    expect($service->shouldSend(new Exception('test'), $request))->toBeTrue();
});
