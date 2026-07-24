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

        // Identity lives in the Laravel session (SESSION_LIFETIME), not the
        // 5-minute API token. The token is only used once at login to cache
        // the profile photo, so we no longer refresh it here or log the user
        // out when it ages — that was the source of the per-request stalls and
        // the login rate-limit storm.

        // Validate user office data
        if (!isset($user['office']['id'])) {
            session()->invalidate();
            session()->regenerateToken();
            session()->flash('error', 'Invalid user session data. Please login again.');
            return redirect()->route('login');
        }

        return $next($request);
    }
}
