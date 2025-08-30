<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Change sessions.user_id to VARCHAR(26) to store ULIDs
        DB::statement("ALTER TABLE `sessions` MODIFY COLUMN `user_id` VARCHAR(26) NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to unsigned big integer. Adjust if your original type differs.
        DB::statement("ALTER TABLE `sessions` MODIFY COLUMN `user_id` BIGINT UNSIGNED NULL");
    }
};
