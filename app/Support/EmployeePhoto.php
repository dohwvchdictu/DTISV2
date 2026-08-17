<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Local cache for employee profile photos.
 *
 * The API token issued at login expires in five minutes and is used for
 * nothing but fetching this image, so the copy has to be taken while the
 * token is fresh and then served from disk for the rest of the session.
 *
 * Files are keyed by employee id, not by the bare filename: two employees
 * whose photos happen to share a filename would otherwise collide and be
 * shown each other's picture.
 */
class EmployeePhoto
{
    /** Photo fetching must never hold up the login response for long. */
    protected const FETCH_TIMEOUT_SECONDS = 5;

    /** Anything smaller than this is an error page, not an image. */
    protected const MIN_IMAGE_BYTES = 100;

    /**
     * Storage path for this employee's cached photo, or null when the
     * employee has no photo or it is an external URL we do not cache.
     */
    public static function path(array $employee): ?string
    {
        $photoUrl = $employee['photoUrl'] ?? null;

        if (!$photoUrl || filter_var($photoUrl, FILTER_VALIDATE_URL)) {
            return null;
        }

        $filename = basename(str_replace('\\', '/', $photoUrl));

        if ($filename === '' || $filename === '.' || $filename === '..') {
            return null;
        }

        $id = $employee['id'] ?? null;

        return 'photos/' . ($id !== null ? $id . '-' : '') . $filename;
    }

    /**
     * Public URL of the already-cached photo, or null if nothing is cached.
     * Never touches the network, so it is safe on every page render.
     */
    public static function cachedUrl(array $employee): ?string
    {
        $photoUrl = $employee['photoUrl'] ?? null;

        // An external URL is already directly usable.
        if ($photoUrl && filter_var($photoUrl, FILTER_VALIDATE_URL)) {
            return $photoUrl;
        }

        $path = self::path($employee);

        if ($path && Storage::disk('public')->exists($path)) {
            return asset('storage/' . $path);
        }

        // Photos cached by an older build were keyed on the filename alone.
        if ($photoUrl) {
            $legacy = 'photos/' . basename(str_replace('\\', '/', $photoUrl));
            if (Storage::disk('public')->exists($legacy)) {
                return asset('storage/' . $legacy);
            }
        }

        return null;
    }

    /**
     * Fetch and store the photo if it is not cached already. Intended to run
     * after the response has been sent — failures are logged and otherwise
     * ignored, since the navbar falls back to a default avatar.
     */
    public static function cache(array $employee, ?string $token): void
    {
        $path = self::path($employee);

        if (!$path || !$token || Storage::disk('public')->exists($path)) {
            return;
        }

        try {
            $response = Http::withToken($token)
                ->timeout(self::FETCH_TIMEOUT_SECONDS)
                ->get(config('services.api.base_url') . 'employee/image/' . urlencode($employee['photoUrl']));

            if ($response->successful() && strlen($response->body()) > self::MIN_IMAGE_BYTES) {
                Storage::disk('public')->put($path, $response->body());
            }
        } catch (\Exception $e) {
            Log::warning('Could not cache employee photo at login', [
                'employee_id' => $employee['id'] ?? null,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
