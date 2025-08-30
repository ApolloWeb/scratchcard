<?php

namespace Database\Seeders;

use App\Models\PlaySession;
use App\Models\GenerationBatch;
use Illuminate\Database\Seeder;

class PlaySessionSeeder extends Seeder
{
    public function run(): void
    {
        $batches = GenerationBatch::all();
        if ($batches->isEmpty()) {
            $batches = GenerationBatch::factory()->count(2)->create();
        }

        foreach ($batches as $batch) {
            PlaySession::factory()->count(10)->create(['batch_id' => $batch->id]);
        }
    }
}
