<?php

use App\Mail\ErrorLogMail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Mail::fake();
    Cache::flush();
    config(['error-logging.email' => 'admin@example.com']);
});

it('sends email when exception occurs', function () {
    Route::get('/test-error', function () {
        throw new RuntimeException('Test exception');
    });

    $this->get('/test-error');

    Mail::assertQueued(ErrorLogMail::class, function ($mail) {
        return $mail->hasTo(config('error-logging.email'));
    });
});

it('does not send email for 404', function () {
    $this->get('/nonexistent-page-'.uniqid());

    Mail::assertNothingSent();
});

it('does not send duplicate email within 5 minutes', function () {
    Route::get('/test-error-dup', function () {
        throw new RuntimeException('Duplicate test');
    });

    $this->get('/test-error-dup');
    $this->get('/test-error-dup');

    Mail::assertQueued(ErrorLogMail::class, 1);
});

it('sends email for different URLs', function () {
    Route::get('/test-error-a', function () {
        throw new RuntimeException('Error A');
    });
    Route::get('/test-error-b', function () {
        throw new RuntimeException('Error B');
    });

    $this->get('/test-error-a');
    $this->get('/test-error-b');

    Mail::assertQueued(ErrorLogMail::class, 2);
});

it('does not send email when ERROR_LOG_EMAIL is not set', function () {
    config(['error-logging.email' => null]);

    Route::get('/test-error-no-email', function () {
        throw new RuntimeException('No email');
    });

    $this->get('/test-error-no-email');

    Mail::assertNothingSent();
});
