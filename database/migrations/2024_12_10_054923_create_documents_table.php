<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id');
            $table->foreignId('bundle_id')->nullable();
            $table->foreignId('citizen_charter_id')->nullable();
            // document related details
            $table->integer('office_id');
            $table->integer('user_id');
            $table->integer('assigned_to')->nullable();
            $table->string('control_no')->nullable();
            $table->enum('source', ['internal', 'external'])->default('internal');
            $table->boolean('is_arta')->default(false);
            $table->boolean('is_bundle')->default(false);
            $table->string('subject', 750);
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
