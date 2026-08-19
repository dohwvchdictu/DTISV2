<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * One-time data fix: make the stored instant the real instant.
 *
 * Every date column here is a MySQL TIMESTAMP, which MySQL keeps internally in
 * UTC and converts through the *session* time zone on each read and write. The
 * connection pinned that session to +00:00 while PHP ran on Asia/Manila, so
 * Laravel wrote a Manila wall clock and MySQL filed it as though it were UTC —
 * leaving every stored instant 8 hours ahead of reality. Reads converted back
 * through the same wrong zone, which is why the screens always looked right.
 *
 * This shifts the stored values back 8 hours; the session zone moves to +08:00
 * in config/database.php in the same maintenance window. Displays do not change:
 *
 *   before:  internal 13:27Z --session +00:00--> 13:27 --cast Manila--> 13:27
 *   after:   internal 05:27Z --session +08:00--> 13:27 --cast Manila--> 13:27
 *
 * The arithmetic is session-zone agnostic. `col - INTERVAL 8 HOUR` is computed
 * on the session-local representation and written back through the same
 * conversion, so it moves the internal UTC value by exactly -8h whichever zone
 * is active — meaning it does not matter whether the config flip lands before
 * or after this runs, only that the application is down for both.
 *
 * RUN ONCE. Everything happens in a single transaction so a failure cannot
 * leave the data half-shifted, and down() reverses it.
 */
return new class extends Migration
{
    private const SHIFT_HOURS = 8;

    /** Rows are updated in primary-key ranges of this size. */
    private const CHUNK = 50000;

    /**
     * failed_jobs.failed_at is written by MySQL's own CURRENT_TIMESTAMP
     * (useCurrent() in its migration), so those values are already the true
     * instant and must not move. migrations has no timestamp columns but is
     * excluded on principle — it is bookkeeping, not application data.
     */
    private const SKIP_TABLES = ['failed_jobs', 'migrations'];

    public function up(): void
    {
        // Order guard, not an arithmetic one. The shift works whichever session
        // zone is active, but running it while the connection is still pinned to
        // +00:00 would leave every screen reading 8 hours early until the config
        // catches up. Flip config/database.php to '+08:00' first, in the same
        // maintenance window, so a stray `php artisan migrate` cannot do this.
        $pinned = config('database.connections.mysql.timezone');

        if ($pinned !== '+08:00') {
            throw new RuntimeException(
                "Refusing to shift timestamps while the mysql connection is pinned to '{$pinned}'. "
                . "Set config/database.php connections.mysql.timezone to '+08:00' first — see this file's docblock."
            );
        }

        $this->shift('-');
    }

    public function down(): void
    {
        $this->shift('+');
    }

    private function shift(string $sign): void
    {
        $tables = $this->timestampColumns();

        if (empty($tables)) {
            throw new RuntimeException('No TIMESTAMP columns found — refusing to run against an unexpected schema.');
        }

        DB::transaction(function () use ($tables, $sign) {
            foreach ($tables as $table => $columns) {
                $affected = $this->shiftTable($table, $columns, $sign);

                echo sprintf(
                    '  %s: %s %s %d hours (%d rows)%s',
                    $table,
                    implode(', ', $columns),
                    $sign === '-' ? 'back' : 'forward',
                    self::SHIFT_HOURS,
                    $affected,
                    PHP_EOL
                );
            }
        });
    }

    /**
     * @param  string[]  $columns
     */
    private function shiftTable(string $table, array $columns, string $sign): int
    {
        $set = implode(', ', array_map(
            fn ($column) => sprintf(
                '`%s` = `%s` %s INTERVAL %d HOUR',
                $column,
                $column,
                $sign,
                self::SHIFT_HOURS
            ),
            $columns
        ));

        // Tables without an auto-incrementing key (password_reset_tokens) are
        // small enough to move in one statement.
        if (!$this->hasIdColumn($table)) {
            return DB::update("update `{$table}` set {$set}");
        }

        $max = (int) DB::table($table)->max('id');
        $affected = 0;

        for ($low = 1; $low <= $max; $low += self::CHUNK) {
            $affected += DB::update(
                "update `{$table}` set {$set} where `id` >= ? and `id` < ?",
                [$low, $low + self::CHUNK]
            );
        }

        return $affected;
    }

    /**
     * Every TIMESTAMP column in the current schema, grouped by table, so a
     * column added later is not silently left behind.
     *
     * @return array<string, string[]>
     */
    private function timestampColumns(): array
    {
        $rows = DB::select(
            "select table_name as table_name, column_name as column_name
             from information_schema.columns
             where table_schema = database()
               and data_type = 'timestamp'
             order by table_name, ordinal_position"
        );

        $tables = [];

        foreach ($rows as $row) {
            $table = $row->table_name;
            $column = $row->column_name;

            if (in_array($table, self::SKIP_TABLES, true)) {
                continue;
            }

            $this->assertSafeIdentifier($table);
            $this->assertSafeIdentifier($column);

            $tables[$table][] = $column;
        }

        return $tables;
    }

    private function hasIdColumn(string $table): bool
    {
        return (bool) DB::selectOne(
            "select 1 as found
             from information_schema.columns
             where table_schema = database()
               and table_name = ?
               and column_name = 'id'",
            [$table]
        );
    }

    /**
     * These names come from information_schema rather than user input, but they
     * are interpolated into SQL, so they are checked rather than trusted.
     */
    private function assertSafeIdentifier(string $identifier): void
    {
        if (!preg_match('/^[A-Za-z0-9_]+$/', $identifier)) {
            throw new RuntimeException("Unsafe identifier in schema: {$identifier}");
        }
    }
};
