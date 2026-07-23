<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The Status-of-Documents report (page + print) aggregates logs by
     * "action_id IN (Forwarded, Closed) within a date range, grouped by
     * assigned_to". No existing index covers action_id + created_at, so that
     * aggregate scanned a large slice of the 500k+ row logs table (~1.2s).
     * This composite makes it a covering, index-only scan.
     */
    public function up(): void
    {
        Schema::table('logs', function (Blueprint $table) {
            $table->index(['action_id', 'created_at', 'assigned_to'], 'logs_action_created_assigned_index');
        });
    }

    public function down(): void
    {
        Schema::table('logs', function (Blueprint $table) {
            $table->dropIndex('logs_action_created_assigned_index');
        });
    }
};
