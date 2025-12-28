<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

    public function handle(Request $request, Closure $next): Response
    {

        if (Auth::check() && Auth::user()->hasRole('Super Admin')) {
            return $next($request);
        }

        if (Auth::check() && Auth::user()->hasRole('Admin')) {
            return $next($request);
        }


        abort(403, 'Vous n\'avez pas les droits nécessaires.');
    }
}
