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
            $table->ulid('batch_id')->nullable();
            $table->string('code')->unique();
            $table->uuid('masked_token');
            $table->enum('status', ['NEW', 'SCRATCHING', 'REVEALED', 'EXPIRED'])->default('NEW');
            $table->enum('outcome', ['WIN', 'LOSE'])->nullable();
            $table->ulid('prize_tier_id')->nullable();
            $table->unsignedInteger('payout_minor')->default(0);
            $table->tinyInteger('scratch_pct')->default(0);
            $table->unsignedInteger('scratch_duration')->default(0);
            $table->timestamp('revealed_at')->nullable();
            $table->datetime('expires_at')->nullable();
            $table->string('server_seed_hash')->nullable();
            $table->string('client_seed')->nullable();
            $table->unsignedInteger('nonce')->default(0);
            $table->string('ip')->nullable();
            $table->string('ua')->nullable();
            $table->tinyInteger('fraud_score')->default(0);
            $table->string('referrer')->nullable();
            $table->json('session_data')->nullable();
            $table->timestamps();

            $table->foreign('batch_id')->references('id')->on('generation_batches')->onDelete('set null');
            $table->foreign('prize_tier_id')->references('id')->on('prize_tiers')->onDelete('set null');

            $table->index(['code']);
            $table->index(['masked_token']);
            $table->index(['created_at', 'status']);
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
