<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * Turns document logs into display-ready timeline rows.
 *
 * The routing trail is rendered in four places (the document, pending and
 * incoming detail modals and the global search modal). Keeping the label
 * rules here stops those copies drifting apart the way they already had.
 *
 * Logs must arrive newest-first (created_at DESC, id DESC): both the
 * Forwarded / For Receiving pairing and the elapsed column rely on that order.
 */
class DocumentTimeline
{
    /**
     * A Forwarded log and the For Receiving log it produces are written
     * moments apart by the same user. Anything wider than this is a separate
     * hop and is left as its own row.
     */
    private const PAIR_WINDOW_SECONDS = 300;

    /** Shown in place of an office whose name the directory API cannot resolve. */
    private const UNKNOWN_OFFICE = '—';

    /**
     * @param  iterable  $logs        Log models or plain arrays, newest-first.
     * @param  callable  $office      fn ($officeId) => office name
     * @param  callable  $user        fn ($employeeId) => employee name
     */
    public static function build($logs, callable $office, callable $user): array
    {
        $normalized = [];

        foreach ($logs as $log) {
            $normalized[] = self::normalize($log);
        }

        return self::present(self::mergeForwardPairs($normalized), $office, $user);
    }

    /**
     * Where the document sits now, taken from the newest row.
     */
    public static function currentLocation(array $rows): ?string
    {
        $offices = $rows[0]['offices'] ?? [];

        return $offices['To'] ?? $offices['Office'] ?? $offices['From'] ?? null;
    }

    /**
     * Flatten a Log model or an already-arrayed log into one shape.
     */
    private static function normalize($log): array
    {
        $action = self::get($log, 'action');
        $user = self::get($log, 'user');
        $createdAt = self::get($log, 'created_at');

        return [
            'id' => self::get($log, 'id'),
            'action' => self::get($action, 'name'),
            'color' => self::get($action, 'color'),
            'created_at' => self::localTime($createdAt),
            'description' => self::get($log, 'description'),
            'remarks' => self::get($log, 'remarks'),
            'office_id' => self::get($log, 'office_id'),
            'assigned_to' => self::get($log, 'assigned_to'),
            'endorsed_to' => self::get($log, 'endorsed_to'),
            'user_id' => self::get($log, 'user_id'),
            'user_name' => self::get($user, 'name'),
        ];
    }

    /**
     * Timestamps reach this class two ways: as Carbon casts off a Log model,
     * already in the app timezone, and as strings off $log->toArray(), which
     * Eloquent serializes to UTC. Pinning both to the app timezone keeps the
     * search modal showing the same wall clock as the detail pages.
     */
    private static function localTime($value): ?Carbon
    {
        if (!$value) {
            return null;
        }

        return Carbon::parse($value)->timezone(config('app.timezone'));
    }

    /**
     * Read a key from either an array or an object (Log model, Action relation).
     */
    private static function get($subject, string $key)
    {
        if (is_array($subject)) {
            return $subject[$key] ?? null;
        }

        if (is_object($subject)) {
            return $subject->{$key} ?? null;
        }

        return null;
    }

    /**
     * Forwarding writes two logs — "Forwarded" from the sending office and
     * "For Receiving" for the destination — so every hop cost two rows and
     * repeated its remark. Collapse the pair into a single From → To row.
     */
    private static function mergeForwardPairs(array $rows): array
    {
        $merged = [];
        $count = count($rows);

        for ($i = 0; $i < $count; $i++) {
            $row = $rows[$i];
            $older = $rows[$i + 1] ?? null;

            if (self::isForwardPair($row, $older)) {
                $merged[] = array_merge($older, [
                    'to_office_id' => $row['assigned_to'],
                    'endorsed_to' => $older['endorsed_to'] ?: $row['endorsed_to'],
                    'remarks' => $older['remarks'] ?: $row['remarks'],
                ]);

                $i++; // the For Receiving half has been absorbed

                continue;
            }

            $merged[] = $row;
        }

        return $merged;
    }

    private static function isForwardPair(array $receiving, ?array $forwarded): bool
    {
        return $forwarded !== null
            && $receiving['action'] === 'For Receiving'
            && $forwarded['action'] === 'Forwarded'
            && $receiving['user_id'] == $forwarded['user_id']
            && $receiving['created_at']
            && $forwarded['created_at']
            && abs($receiving['created_at']->diffInSeconds($forwarded['created_at'])) <= self::PAIR_WINDOW_SECONDS;
    }

    private static function present(array $rows, callable $office, callable $user): array
    {
        $presented = [];

        foreach ($rows as $index => $row) {
            $previous = $rows[$index + 1]['created_at'] ?? null;
            // Employee names arrive as "first last suffix" and trail a space
            // whenever the suffix is empty, which is most of the directory.
            $endorsedTo = $row['endorsed_to'] ? trim((string) ($user)($row['endorsed_to'])) : null;
            $actor = trim((string) ($row['user_name'] ?: ($user)($row['user_id'])));

            $presented[] = [
                'key' => 'log-' . ($row['id'] ?? $index),
                'action' => $row['action'] ?: 'Document Activity',
                'color' => $row['color'] ?: 'bg-gray-100',
                'created_at' => $row['created_at'],
                'description' => $row['description'] ?: null,
                'offices' => self::officeRows($row, $office),
                'endorsed_to' => $endorsedTo ?: null,
                'user' => $actor ?: null,
                'remarks' => $row['remarks'] ?: null,
                'elapsed' => self::elapsed($row['created_at'], $previous),
            ];
        }

        return $presented;
    }

    /**
     * Which offices a row shows, and under what label.
     *
     * "Returned" carries both directions: office_id is the office sending it
     * back, assigned_to is where it is going. A merged forward hop likewise
     * knows both ends. Everything else acts in a single office.
     */
    private static function officeRows(array $row, callable $office): array
    {
        $name = function ($id) use ($office) {
            if ($id === null || $id === '') {
                return null;
            }

            // Keep the row when the directory API is down rather than
            // silently rendering a trail with no offices in it at all.
            return ($office)($id) ?: self::UNKNOWN_OFFICE;
        };

        if (array_key_exists('to_office_id', $row)) {
            $rows = ['From' => $name($row['office_id']), 'To' => $name($row['to_office_id'])];
        } elseif ($row['action'] === 'Returned') {
            $rows = ['From' => $name($row['office_id']), 'To' => $name($row['assigned_to'])];
        } elseif ($row['action'] === 'For Receiving') {
            $rows = ['To' => $name($row['assigned_to'])];
        } elseif ($row['action'] === 'Forwarded') {
            $rows = ['From' => $name($row['office_id'])];
        } else {
            $rows = ['Office' => $name($row['office_id'])];
        }

        return array_filter($rows, fn ($value) => $value !== null);
    }

    /**
     * How long the document sat between the previous step and this one.
     */
    private static function elapsed(?Carbon $current, ?Carbon $previous): ?string
    {
        if (!$current || !$previous) {
            return null;
        }

        $minutes = (int) abs($current->diffInMinutes($previous));

        if ($minutes < 1) {
            return null;
        }

        $days = intdiv($minutes, 1440);
        $hours = intdiv($minutes % 1440, 60);
        $remaining = $minutes % 60;

        if ($days > 0) {
            return $hours > 0 ? "{$days}d {$hours}h" : "{$days}d";
        }

        if ($hours > 0) {
            return $remaining > 0 ? "{$hours}h {$remaining}m" : "{$hours}h";
        }

        return "{$remaining}m";
    }
}
