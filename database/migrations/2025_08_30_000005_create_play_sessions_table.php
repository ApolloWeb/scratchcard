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
        Schema::create('play_sessions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('batch_id');
            $table->string('code', 12)->unique();
            $table->enum('status', ['NEW', 'SCRATCHING', 'REVEALED', 'EXPIRED'])->default('NEW');
            $table->enum('outcome', ['WIN', 'LOSE']);
            $table->ulid('prize_tier_id')->nullable();
            $table->unsignedInteger('payout_minor')->default(0);
            $table->unsignedTinyInteger('scratch_pct')->default(0);
            $table->unsignedInteger('scratch_duration')->default(0);
            $table->timestamp('revealed_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            // provably fair
            $table->string('server_seed_hash');
            $table->text('server_seed_encrypted');
            $table->string('client_seed')->nullable();
            $table->unsignedInteger('nonce')->default(0);

            $table->string('ip')->nullable();
            $table->string('ua')->nullable();

            $table->timestamps();

            $table->index('batch_id');
            $table->index('status');
            $table->index('expires_at');

            $table->foreign('batch_id')->references('id')->on('generation_batches')->cascadeOnDelete();
            $table->foreign('prize_tier_id')->references('id')->on('prize_tiers')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('play_sessions');
    }
};
