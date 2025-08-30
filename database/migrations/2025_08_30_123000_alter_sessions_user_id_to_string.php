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
        // Before converting back to BIGINT, clear any non-numeric values to avoid truncation warnings.
        // This will set ULIDs or other non-numeric identifiers to NULL so the ALTER can succeed.
        DB::statement("UPDATE `sessions` SET `user_id` = NULL WHERE `user_id` IS NOT NULL AND `user_id` != '' AND `user_id` REGEXP '[^0-9]'");

        // Revert to unsigned big integer. Adjust if your original type differs.
        DB::statement("ALTER TABLE `sessions` MODIFY COLUMN `user_id` BIGINT UNSIGNED NULL");
    }
};
