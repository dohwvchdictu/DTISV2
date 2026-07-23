<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The "Processed" (Forwarded) and "Closed" status pages, plus the per-employee
     * report, filter logs by (assigned_to, action_id) — e.g. "documents this office
     * forwarded/closed". Without this index those whereHas/join lookups scan the
     * logs table. Composite (assigned_to, action_id) lets them seek directly.
     */
    public function up(): void
    {
        Schema::table('logs', function (Blueprint $table) {
            $table->index(['assigned_to', 'action_id']);
        });
    }

    public function down(): void
    {
        Schema::table('logs', function (Blueprint $table) {
            $table->dropIndex(['assigned_to', 'action_id']);
        });
    }
};
