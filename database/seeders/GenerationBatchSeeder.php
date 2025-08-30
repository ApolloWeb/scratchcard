<?php

namespace Database\Seeders;

use App\Models\GenerationBatch;
use Illuminate\Database\Seeder;

class GenerationBatchSeeder extends Seeder
{
    public function run(): void
    {
        GenerationBatch::factory()->count(4)->create();
    }
}
