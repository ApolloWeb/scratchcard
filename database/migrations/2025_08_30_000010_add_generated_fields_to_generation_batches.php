<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('generation_batches', function (Blueprint $table) {
            if (!Schema::hasColumn('generation_batches', 'created_count')) {
                $table->unsignedInteger('created_count')->default(0);
            }
            if (!Schema::hasColumn('generation_batches', 'status')) {
                $table->string('status')->default('PENDING');
            }
            if (!Schema::hasColumn('generation_batches', 'error')) {
                $table->text('error')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('generation_batches', function (Blueprint $table) {
            if (Schema::hasColumn('generation_batches', 'created_count')) {
                $table->dropColumn('created_count');
            }
            if (Schema::hasColumn('generation_batches', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('generation_batches', 'error')) {
                $table->dropColumn('error');
            }
        });
    }
};
