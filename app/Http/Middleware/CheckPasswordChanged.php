<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPasswordChanged
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user && ! $user->has_changed_password) {
            $panel = Filament::getCurrentPanel();

            if ($panel) {
                $profileRoute = $panel->getProfileUrl();
                $logoutRoute = $panel->getLogoutUrl();

                $currentUrl = $request->url();

                // If it's not the profile page or logout, redirect to profile
                if ($currentUrl !== $profileRoute && $currentUrl !== $logoutRoute) {
                    return redirect($profileRoute);
                }
            }
        }

        return $next($request);
    }
}
