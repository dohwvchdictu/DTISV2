<?php

namespace App\Http\Middleware;

use App\Services\ApiService;
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

        $tokenTtl = config('services.api.token_ttl', 300);
        $refreshThreshold = config('services.api.refresh_threshold', 240);
        $tokenAge = time() - session('token_created_at', 0);

        if ($tokenAge >= $refreshThreshold) {
            $refreshed = app(ApiService::class)->ensureTokenIsFresh();

            // A failed refresh is only fatal once the token is truly past its
            // lifetime — a proactive refresh that hits an API blip while the
            // token is still valid should not log the user out.
            if (!$refreshed && $tokenAge >= $tokenTtl) {
                session(['url.intended' => $request->url()]);
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
