<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class IsStudent
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is logged in AND is a student
        if (Auth::check() && Auth::user()->role === 'student') {
            return $next($request);
        }

        // If not, kick them out (403 Forbidden)
        abort(403, 'Access Denied: Students Only.');
    }
}
