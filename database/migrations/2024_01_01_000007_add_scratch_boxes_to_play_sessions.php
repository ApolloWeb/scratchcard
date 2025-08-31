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
        Schema::table('play_sessions', function (Blueprint $table) {
            // Keep the existing scratch_pct field for backward compatibility
            // Add optional fields for future 3-box scratch system (without breaking current system)
            $table->json('box_symbols')->nullable(); // Array of 3 symbols [symbol1, symbol2, symbol3] - for future use
            $table->json('box_scratch_pct')->nullable(); // Scratch progress for each box - for future use
            $table->unsignedTinyInteger('boxes_revealed')->default(0); // Count of boxes that are fully scratched (>= 75%)
            $table->string('winning_symbol', 10)->nullable(); // The symbol that appears 3 times for winners
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('play_sessions', function (Blueprint $table) {
            // Remove the new fields (keep scratch_pct as it was never removed)
            $table->dropColumn([
                'box_symbols',
                'box_scratch_pct', 
                'boxes_revealed',
                'winning_symbol'
            ]);
        });
    }
};
