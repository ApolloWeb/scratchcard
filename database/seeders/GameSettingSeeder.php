<?php

namespace Database\Seeders;

use App\Models\GameSetting;
use App\Models\Campaign;
use Illuminate\Database\Seeder;

class GameSettingSeeder extends Seeder
{
    public function run(): void
    {
        $campaigns = Campaign::all();
        if ($campaigns->isEmpty()) {
            $campaigns = Campaign::factory()->count(2)->create();
        }

        foreach ($campaigns as $campaign) {
            GameSetting::factory()->create(['campaign_id' => $campaign->id]);
        }
    }
}
