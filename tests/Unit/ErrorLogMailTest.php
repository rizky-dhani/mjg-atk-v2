<?php

use App\Mail\ErrorLogMail;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

use function Pest\Laravel\mock;

it('has correct subject with exception class and URL', function () {
    $exception = new RuntimeException('Something broke');
    $request = Request::create('https://example.com/test-page', 'POST');

    $mail = new ErrorLogMail($exception, $request);

    $envelope = $mail->envelope();

    expect($envelope)->toBeInstanceOf(Envelope::class)
        ->and($envelope->subject)->toBe('[ERROR] RuntimeException at https://example.com/test-page');
});

it('renders the error-log blade view with correct variables', function () {
    $exception = new RuntimeException('Test exception message');
    $request = Request::create('https://example.com/api/test', 'GET', [], [], [], ['REMOTE_ADDR' => '10.0.0.1']);

    $mail = new ErrorLogMail($exception, $request);

    $content = $mail->content();

    expect($content)->toBeInstanceOf(Content::class)
        ->and($content->view)->toBe('emails.error-log');

    $viewData = $content->with;
    expect($viewData['exceptionClass'])->toBe(RuntimeException::class)
        ->and($viewData['exceptionMessage'])->toBe('Test exception message')
        ->and($viewData['requestMethod'])->toBe('GET')
        ->and($viewData['requestUrl'])->toBe('https://example.com/api/test')
        ->and($viewData['clientIp'])->toBe('10.0.0.1')
        ->and($viewData['timestamp'])->toBeString();
});

it('truncates stack trace to first 20 lines', function () {
    // Generate a real exception with a deep stack trace via recursion
    $exception = null;
    $deepThrow = function (int $depth) use (&$deepThrow, &$exception): void {
        if ($depth <= 0) {
            throw new RuntimeException('trace test');
        }
        $deepThrow($depth - 1);
    };

    try {
        $deepThrow(40);
    } catch (RuntimeException $e) {
        $exception = $e;
    }

    $request = Request::create('/test');
    $mail = new ErrorLogMail($exception, $request);

    $viewData = $mail->content()->with;
    $traceLines = explode("\n", $viewData['stackTrace']);

    expect(count($traceLines))->toBe(20);
});

it('attaches error-dump.txt as text/plain', function () {
    $exception = new RuntimeException('Dump test', 42);
    $request = Request::create('https://example.com/page', 'POST', ['key' => 'value']);
    $session = mock(Session::class);
    $session->shouldReceive('all')->andReturn([]);
    $request->setLaravelSession($session);

    $mail = new ErrorLogMail($exception, $request);

    $attachments = $mail->attachments();

    expect($attachments)->toHaveCount(1);

    $attachment = $attachments[0];
    expect($attachment->as)->toBe('error-dump.txt')
        ->and($attachment->mime)->toBe('text/plain');
});

it('includes all sections in the full dump', function () {
    $exception = new RuntimeException('Section test');
    $request = Request::create('/test');
    $session = mock(Session::class);
    $session->shouldReceive('all')->andReturn([]);
    $request->setLaravelSession($session);

    $mail = new ErrorLogMail($exception, $request);

    // Access protected buildFullDump via reflection
    $ref = new ReflectionClass($mail);
    $method = $ref->getMethod('buildFullDump');
    $method->setAccessible(true);
    $content = $method->invoke($mail);

    expect($content)->toContain('=== EXCEPTION ===')
        ->and($content)->toContain('RuntimeException')
        ->and($content)->toContain('Section test')
        ->and($content)->toContain('=== STACK TRACE ===')
        ->and($content)->toContain('=== REQUEST ===')
        ->and($content)->toContain('=== HEADERS ===')
        ->and($content)->toContain('=== SESSION ===')
        ->and($content)->toContain('=== SERVER ===')
        ->and($content)->toContain('=== TIMESTAMP ===');
});

it('filters sensitive fields from request input in dump', function () {
    $exception = new RuntimeException('Filter test');
    $request = Request::create('/test', 'POST', [
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
        'token' => 'abc123',
        'safe_field' => 'visible',
    ]);
    $session = mock(Session::class);
    $session->shouldReceive('all')->andReturn([]);
    $request->setLaravelSession($session);

    $mail = new ErrorLogMail($exception, $request);

    $ref = new ReflectionClass($mail);
    $method = $ref->getMethod('buildFullDump');
    $method->setAccessible(true);
    $content = $method->invoke($mail);

    expect($content)->not->toContain('secret123')
        ->and($content)->not->toContain('abc123')
        ->and($content)->toContain('safe_field');
});
