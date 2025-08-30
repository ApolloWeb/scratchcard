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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->datetime('starts_at');
            $table->datetime('expires_at')->nullable();
            $table->unsignedInteger('max_plays')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('theme_config')->nullable();
            $table->string('locale')->default('en');
            $table->ulid('created_by');
            $table->timestamps();
            
            $table->foreign('created_by')->references('id')->on('admin_users')->onDelete('cascade');
            $table->index(['is_active', 'starts_at', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
