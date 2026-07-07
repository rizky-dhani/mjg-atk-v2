<?php

use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Js;
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
                AuthenticationException::class,
                NotFoundHttpException::class,
                TokenMismatchException::class,
                TooManyRequestsHttpException::class,
            ];

            if (in_array(get_class($e), $excludedClasses)) {
                return null;
            }

            Log::error('Exception occurred', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'url' => $request->fullUrl(),
            ]);

            $traceLines = array_slice(explode("\n", $e->getTraceAsString()), 0, 10);

            $errorDetails = 'Exception: '.get_class($e)."\n"
                .'Message: '.$e->getMessage()."\n"
                .'URL: '.$request->fullUrl()."\n"
                .'Method: '.$request->method()."\n"
                .'IP: '.$request->ip()."\n"
                .'Stack Trace:'."\n".implode("\n", $traceLines);

            $errorDetailsJs = Js::from($errorDetails)->toHtml();

            Notification::make()
                ->title('Terjadi kesalahan')
                ->body('Tekan tombol Copy Error untuk menyalin detail ke clipboard.')
                ->persistent()
                ->danger()
                ->actions([
                    Action::make('copyError')
                        ->label('Copy Error')
                        ->color('danger')
                        ->alpineClickHandler('navigator.clipboard.writeText('.$errorDetailsJs.')'),
                ])
                ->send();

            User::role('Super Admin')->each(
                fn ($admin) => Notification::make()
                    ->title('Error: '.get_class($e))
                    ->body($errorDetails)
                    ->danger()
                    ->actions([
                        Action::make('copyError')
                            ->label('Copy Error')
                            ->color('danger')
                            ->alpineClickHandler('navigator.clipboard.writeText('.$errorDetailsJs.')'),
                    ])
                    ->sendToDatabase($admin)
            );

            return null;
        });
    })->create();
