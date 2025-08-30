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
            $table->string('label'); // e.g. "£1"
            $table->unsignedInteger('amount_minor'); // amount in pence
            $table->unsignedInteger('weight'); // relative weight among wins
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
