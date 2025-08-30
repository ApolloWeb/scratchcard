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
            $table->string('code', 10)->unique(); // 8-10 chars, Crockford Base32
            $table->enum('status', ['NEW', 'SCRATCHING', 'REVEALED', 'EXPIRED'])->default('NEW');
            $table->enum('outcome', ['WIN', 'LOSE']); // decided at generation
            $table->ulid('prize_tier_id')->nullable();
            $table->unsignedInteger('payout_minor')->default(0);
            $table->unsignedTinyInteger('scratch_pct')->default(0); // 0-100
            $table->unsignedInteger('scratch_duration')->default(0); // milliseconds
            $table->timestamp('revealed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            
            // Provably-fair fields
            $table->string('server_seed_hash'); // SHA-256 hash of server seed
            $table->text('server_seed_encrypted')->nullable(); // encrypted server seed
            $table->string('client_seed')->nullable();
            $table->unsignedInteger('nonce')->default(0);
            
            // Tracking fields
            $table->string('ip', 45)->nullable();
            $table->text('ua')->nullable(); // user agent
            
            $table->timestamps();
            
            // Indexes
            $table->index('batch_id');
            $table->index('status');
            $table->index('expires_at');
            
            // Foreign keys
            $table->foreign('batch_id')->references('id')->on('generation_batches')->onDelete('cascade');
            $table->foreign('prize_tier_id')->references('id')->on('prize_tiers')->onDelete('set null');
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
