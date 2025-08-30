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
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('play_session_id')->nullable();
            $table->string('event_type');
            $table->json('properties')->nullable();
            $table->timestamp('timestamp');
            $table->timestamp('processed_at')->nullable();
            
            $table->foreign('play_session_id')->references('id')->on('play_sessions')->onDelete('cascade');
            $table->index(['play_session_id', 'timestamp']);
            $table->index(['event_type', 'timestamp']);
            $table->index(['timestamp']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};
