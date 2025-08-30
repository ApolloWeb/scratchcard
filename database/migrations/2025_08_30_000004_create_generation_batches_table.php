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
        Schema::create('generation_batches', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name')->nullable();
            $table->unsignedInteger('requested_count');
            $table->unsignedInteger('created_count')->default(0);
            $table->enum('status', ['PENDING', 'RUNNING', 'COMPLETED', 'FAILED'])->default('PENDING');
            $table->unsignedInteger('win_numerator');
            $table->unsignedInteger('win_denominator');
            $table->unsignedTinyInteger('code_length')->default(8);
            $table->json('settings_snapshot');
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generation_batches');
    }
};
