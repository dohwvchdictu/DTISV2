<?php

namespace App\Http\Middleware;

use App\Services\ApiService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
            // fullUrl(), not url() — otherwise a user bounced off a filtered
            // report comes back to the unfiltered one after signing in.
            session(['url.intended' => $request->fullUrl()]);
            return redirect()->route('login');
        }

        // Validate user office data
        if (!isset($user['office']['id'])) {
            return $this->endSession('Invalid user session data. Please login again.');
        }

        // Identity lives in the Laravel session (SESSION_LIFETIME), not the
        // 5-minute API token. The token is only used once at login to cache
        // the profile photo, so we no longer refresh it here or log the user
        // out when it ages — that was the source of the per-request stalls and
        // the login rate-limit storm. What the session *does* still need is a
        // ceiling on its own age, and a periodic check that the account behind
        // it is still valid.
        if ($response = $this->enforceAbsoluteLifetime()) {
            return $response;
        }

        if ($response = $this->revalidateAccount($user)) {
            return $response;
        }

        return $next($request);
    }

    /**
     * SESSION_LIFETIME is an idle timeout that resets on every request, so an
     * everyday user is never signed out. This applies a hard ceiling measured
     * from the moment of login.
     */
    protected function enforceAbsoluteLifetime(): ?Response
    {
        $minutes = (int) config('session.absolute_lifetime', 0);

        if ($minutes <= 0) {
            return null;
        }

        $createdAt = (int) session('token_created_at', 0);

        // Sessions that predate this check have no login timestamp. Stamp them
        // now rather than signing everybody out the moment this deploys.
        if ($createdAt <= 0) {
            session(['token_created_at' => time()]);
            return null;
        }

        if ((time() - $createdAt) < ($minutes * 60)) {
            return null;
        }

        Log::info('Session reached its absolute lifetime', [
            'employee_id' => session('user')['id'] ?? null,
            'age_minutes' => (int) ((time() - $createdAt) / 60),
        ]);

        return $this->endSession('Your session has expired. Please sign in again.');
    }

    /**
     * Re-check the signed-in account against the employee directory so an
     * account that has been removed loses access mid-session, and an office
     * transfer is picked up without waiting for the user to sign out.
     *
     * Reads the directory through ApiService's existing cache, so this costs
     * no API call on the overwhelming majority of requests. Anything the
     * directory cannot answer fails open — an API outage must never sign the
     * whole office out.
     */
    protected function revalidateAccount(array $user): ?Response
    {
        $interval = (int) config('session.revalidate_minutes', 0);

        if ($interval <= 0) {
            return null;
        }

        $lastChecked = (int) session('revalidated_at', 0);

        if ($lastChecked > 0 && (time() - $lastChecked) < ($interval * 60)) {
            return null;
        }

        $employeeId = $user['id'] ?? null;

        // Stamp before deciding anything, so a directory outage retries on the
        // next interval instead of on every single request.
        session(['revalidated_at' => time()]);

        if ($employeeId === null) {
            return null;
        }

        $result = app(ApiService::class)->lookupEmployee($employeeId);

        // Directory unreachable — fail open.
        if (!$result['available']) {
            return null;
        }

        $employee = $result['employee'];

        // The account is gone from the directory entirely.
        if ($employee === null) {
            Log::warning('Signed-in employee is no longer in the directory', [
                'employee_id' => $employeeId,
            ]);

            return $this->endSession('Your account is no longer listed in the employee directory. Please contact the HRIS team.');
        }

        // An employee flagged inactive. This is opt-in: see the note on
        // SESSION_REVALIDATE_EMPLOYMENT in .env — until the meaning of the
        // flag is confirmed against live data, we only record what we would
        // have done rather than signing the person out.
        if (($employee['employmentStatus'] ?? true) === false) {
            if (config('session.revalidate_employment', false)) {
                Log::warning('Signed-in employee is flagged inactive', [
                    'employee_id' => $employeeId,
                ]);

                return $this->endSession('Your account is no longer active. Please contact the HRIS team.');
            }

            Log::info('Revalidation: employee flagged inactive (not enforced)', [
                'employee_id' => $employeeId,
                'employee_no' => $employee['employeeId'] ?? null,
            ]);
        }

        // Office transfers: keep the session in step with the directory, since
        // every module scopes its documents by session office id.
        $sessionOffice = $user['office']['id'] ?? null;
        $directoryOffice = $employee['office']['id'] ?? null;

        if ($directoryOffice !== null && (string) $directoryOffice !== (string) $sessionOffice) {
            Log::info('Employee office changed; refreshing session', [
                'employee_id' => $employeeId,
                'from' => $sessionOffice,
                'to' => $directoryOffice,
            ]);

            $user['office'] = $employee['office'];
            session(['user' => $user]);
        }

        return null;
    }

    /**
     * Tear the session down and send the user back to the login page with an
     * explanation. The message is flashed *after* invalidate(), which wipes
     * everything already in the session.
     */
    protected function endSession(string $message): Response
    {
        session()->invalidate();
        session()->regenerateToken();
        session()->flash('error', $message);

        return redirect()->route('login');
    }
}
