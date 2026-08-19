<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Guards the one invariant that silently breaks every date in the system: the
 * database session time zone and the application time zone must agree, and the
 * stored TIMESTAMP values must carry the instant their displayed value implies.
 *
 * These columns are MySQL TIMESTAMPs, converted through the session zone on
 * every read and write. When the two zones disagree, reads and writes still
 * cancel out — so the screens keep looking correct while the stored instant
 * drifts by the difference, and nothing surfaces until another host, another
 * client, or MySQL's own NOW() reads the same rows. Run this after every
 * deploy and after any change to config/app.php or config/database.php.
 */
class CheckTimezone extends Command
{
    protected $signature = 'dtis:check-timezone';

    protected $description = 'Verify the database session time zone matches the application time zone and that stored timestamps carry the correct instant';

    public function handle(): int
    {
        $failures = 0;

        $failures += $this->checkSessionOffset();
        $failures += $this->checkStoredInstant();
        $failures += $this->checkClockDrift();

        if ($failures > 0) {
            $this->newLine();
            $this->error("{$failures} check(s) failed — see database/migrations/2026_08_19_000000_shift_timestamps_to_true_utc.php for the background.");

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Time zone configuration is consistent.');

        return self::SUCCESS;
    }

    /**
     * The session zone must carry the same UTC offset as the app zone.
     *
     * Read as an offset rather than a name so 'SYSTEM', '+08:00' and
     * 'Asia/Manila' are all comparable.
     */
    private function checkSessionOffset(): int
    {
        $databaseOffset = (int) DB::selectOne(
            'select timestampdiff(second, utc_timestamp(), now()) as offset_seconds'
        )->offset_seconds;

        $applicationOffset = now()->utcOffset() * 60;

        if ($databaseOffset === $applicationOffset) {
            $this->line(sprintf(
                '  <info>OK</info>   session offset %s matches %s (%s)',
                $this->formatOffset($databaseOffset),
                config('app.timezone'),
                $this->formatOffset($applicationOffset)
            ));

            return 0;
        }

        $this->line(sprintf(
            '  <error>FAIL</error> session offset %s but %s is %s — every timestamp written now is off by %d hour(s)',
            $this->formatOffset($databaseOffset),
            config('app.timezone'),
            $this->formatOffset($applicationOffset),
            ($applicationOffset - $databaseOffset) / 3600
        ));

        return 1;
    }

    /**
     * The real assertion: for the newest document, the instant MySQL holds must
     * be the instant its displayed value means in the application time zone.
     */
    private function checkStoredInstant(): int
    {
        $row = DB::selectOne(
            'select id, created_at, unix_timestamp(created_at) as stored_instant
             from documents
             where created_at is not null
             order by id desc
             limit 1'
        );

        if (!$row) {
            $this->line('  <comment>SKIP</comment> no documents to check the stored instant against');

            return 0;
        }

        $expected = Carbon::parse($row->created_at, config('app.timezone'))->timestamp;
        $drift = (int) $row->stored_instant - $expected;

        if ($drift === 0) {
            $this->line(sprintf(
                '  <info>OK</info>   document %d stored as %s, which is the instant it displays',
                $row->id,
                $row->created_at
            ));

            return 0;
        }

        $this->line(sprintf(
            '  <error>FAIL</error> document %d displays %s but is stored as %sZ — %d hour(s) off',
            $row->id,
            $row->created_at,
            gmdate('Y-m-d H:i:s', (int) $row->stored_instant),
            $drift / 3600
        ));

        return 1;
    }

    /**
     * A wall-clock difference between the two hosts would move new rows without
     * either zone being misconfigured.
     */
    private function checkClockDrift(): int
    {
        $drift = abs(now()->timestamp - (int) DB::selectOne('select unix_timestamp() as now')->now);

        if ($drift <= 60) {
            $this->line("  <info>OK</info>   application and database clocks agree (within {$drift}s)");

            return 0;
        }

        $this->line("  <error>FAIL</error> application and database clocks differ by {$drift}s");

        return 1;
    }

    private function formatOffset(int $seconds): string
    {
        return sprintf('%s%02d:%02d', $seconds < 0 ? '-' : '+', intdiv(abs($seconds), 3600), intdiv(abs($seconds) % 3600, 60));
    }
}
