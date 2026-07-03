<?php

use App\Mail\ErrorLogMail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Cache::flush();
    config(['error-logging.email' => 'admin@example.com']);
});

it('sends error email on uncaught exception', function () {
    Mail::fake();

    Route::get('/test-throw-exception', fn () => throw new RuntimeException('test exception'));

    $this->get('/test-throw-exception');

    Mail::assertQueued(ErrorLogMail::class);
});

it('does not send email for 404 exceptions', function () {
    Mail::fake();

    $this->get('/nonexistent-page');

    Mail::assertNothingSent();
});

it('throttles duplicate error emails within window', function () {
    Mail::fake();

    Route::get('/test-throw-twice', fn () => throw new RuntimeException('throttle test'));

    $this->get('/test-throw-twice');
    Mail::assertQueued(ErrorLogMail::class, 1);

    $this->get('/test-throw-twice');
    Mail::assertQueued(ErrorLogMail::class, 1);
});
