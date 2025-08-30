<?php

namespace Database\Seeders;

use App\Models\GenerationBatch;
use App\Models\Campaign;
use Illuminate\Database\Seeder;

class GenerationBatchSeeder extends Seeder
{
    public function run(): void
    {
        $campaigns = Campaign::all();
        if ($campaigns->isEmpty()) {
            $campaigns = Campaign::factory()->count(2)->create();
        }

        foreach ($campaigns as $campaign) {
            GenerationBatch::factory()->count(2)->create(['campaign_id' => $campaign->id]);
        }
    }
}
