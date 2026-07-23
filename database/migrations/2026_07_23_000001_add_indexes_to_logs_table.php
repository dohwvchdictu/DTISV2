<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The logs table (500k+ rows) had no index beyond PRIMARY, so every
     * per-row `$document->logs` lookup in the status views triggered a full
     * table scan (~226ms each). These indexes make those lookups instant.
     */
    public function up(): void
    {
        Schema::table('logs', function (Blueprint $table) {
            $table->index('document_id');
            $table->index('action_id');
            $table->index(['document_id', 'action_id']);
        });
    }

    public function down(): void
    {
        Schema::table('logs', function (Blueprint $table) {
            $table->dropIndex(['document_id']);
            $table->dropIndex(['action_id']);
            $table->dropIndex(['document_id', 'action_id']);
        });
    }
};
