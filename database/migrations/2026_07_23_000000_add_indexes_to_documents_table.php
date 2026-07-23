<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->index(['assigned_to', 'status']);
            $table->index('bundle_id');
            $table->index('category_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex(['assigned_to', 'status']);
            $table->dropIndex(['bundle_id']);
            $table->dropIndex(['category_id']);
            $table->dropIndex(['created_at']);
        });
    }
};
