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
        Schema::create('prize_tiers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('label');
            $table->text('description')->nullable();
            $table->unsignedInteger('amount_minor');
            $table->unsignedInteger('weight');
            $table->unsignedInteger('max_wins')->nullable();
            $table->unsignedInteger('current_wins')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prize_tiers');
    }
};
