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
        Schema::create('game_settings', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->unsignedInteger('win_numerator');
            $table->unsignedInteger('win_denominator');
            $table->unsignedInteger('reveal_threshold')->default(65);
            $table->unsignedInteger('min_scratch_time')->default(2000);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_settings');
    }
};
