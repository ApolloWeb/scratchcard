<?php

namespace Database\Seeders;

use App\Models\PrizeTier;
use App\Models\Campaign;
use Illuminate\Database\Seeder;

class PrizeTierSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure campaigns exist
        $campaigns = Campaign::all();
        if ($campaigns->isEmpty()) {
            $campaigns = Campaign::factory()->count(2)->create();
        }

        foreach ($campaigns as $campaign) {
            PrizeTier::factory()->count(3)->create(['campaign_id' => $campaign->id]);
        }
    }
}
