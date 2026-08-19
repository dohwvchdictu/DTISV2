<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * Whole business days between two moments, weekends excluded.
 *
 * The reports measure how long a document sat with an office, and an office is
 * not answerable for the weekend. The same "diffInDaysFiltered, skip weekends"
 * expression is hand-rolled in several detail screens; new callers should come
 * here so the Status-of-Documents and Turnaround-Time reports cannot drift
 * apart on what "3 days" means.
 *
 * Whole days only: anything under 24 counted hours is 0, so a same-day
 * turnaround and a six-hour one are indistinguishable by design.
 */
class BusinessDays
{
    public static function between($start, $end): int
    {
        $start = Carbon::parse($start);
        $end = Carbon::parse($end);

        if ($end->lessThanOrEqualTo($start)) {
            return 0;
        }

        return $start->diffInDaysFiltered(function (Carbon $date) {
            return !$date->isWeekend();
        }, $end);
    }
}
