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
        Schema::create('citizen_charters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('office_id');
            $table->boolean('is_external')->default(true);
            $table->integer('required_days');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citizen_charters');
    }
};
