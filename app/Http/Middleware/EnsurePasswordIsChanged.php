<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->force_password_change && ! $request->routeIs('password.*', 'logout')) {
            return redirect()->route('password.change')
                ->with('warning', "Davom etishdan oldin vaqtinchalik parolni almashtiring.");
        }

        return $next($request);
    }
}
