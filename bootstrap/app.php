<?php

use App\Mail\ErrorLogMail;
use App\Services\ErrorThrottleService;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->stopIgnoring(NotFoundHttpException::class);

        $exceptions->renderable(function (Throwable $e, Request $request) {
            $excludedClasses = [
                NotFoundHttpException::class,
                TokenMismatchException::class,
                TooManyRequestsHttpException::class,
            ];

            if (in_array(get_class($e), $excludedClasses)) {
                return null;
            }

            $throttle = app(ErrorThrottleService::class);
            if ($throttle->shouldSend($e, $request)) {
                $email = config('error-logging.email');
                if ($email) {
                    Mail::to($email)->queue(new ErrorLogMail($e, $request));
                }
            }

            Log::error('Exception occurred', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'url' => $request->fullUrl(),
            ]);

            return null;
        });
    })->create();
