<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Citizen's Charter transactions are classified by their charter procedure
     * rather than by document type/category, so the category select is hidden for
     * them and no longer supplies a value.
     *
     * Raw ALTER rather than ->change(): the column carries an index and no foreign
     * key, and doctrine/dbal's column diff on this table would rewrite more than
     * the nullability.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE `documents` MODIFY `category_id` BIGINT UNSIGNED NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE `documents` MODIFY `category_id` BIGINT UNSIGNED NOT NULL');
    }
};
