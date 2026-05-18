<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = session('jwt_token');
        $user = session('user');

        if (!$token || !$user) {
            session(['url.intended' => $request->url()]);
            return redirect()->route('login');
        }

        // Validate user office data
        if (!isset($user['office']['id'])) {
            // Clear invalid session data
            session()->forget(['jwt_token', 'user']);
            session()->flash('error', 'Invalid user session data. Please login again.');
            return redirect()->route('login');
        }

        return $next($request);
    }
}
