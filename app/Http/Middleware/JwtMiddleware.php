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

        // Decode JWT payload and check expiry (no library required — payload is plain base64)
        $parts = explode('.', $token);
        if (count($parts) === 3) {
            $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                session()->invalidate();
                session()->regenerateToken();
                return redirect()->route('login')->with('error', 'Your session has expired. Please login again.');
            }
        }

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
