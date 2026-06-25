<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user && ! $user->is_active) {
            auth()->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $panel = Filament::getCurrentPanel();

            if ($panel) {
                return redirect($panel->getLoginUrl())
                    ->with('error', 'Akun Anda tidak aktif. Hubungi administrator.');
            }
        }

        return $next($request);
    }
}
