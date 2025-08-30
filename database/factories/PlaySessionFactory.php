<?php

namespace Database\Factories;

use App\Models\PlaySession;
use App\Models\Campaign;
use App\Models\GenerationBatch;
use App\Models\PrizeTier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PlaySessionFactory extends Factory
{
    protected $model = PlaySession::class;

    public function definition()
    {
        return [
            'id' => (string) Str::ulid(),
            'campaign_id' => Campaign::factory(),
            'batch_id' => GenerationBatch::factory(),
            'code' => strtoupper($this->faker->bothify('???-####')),
            'masked_token' => Str::random(32),
            'status' => 'pending',
            'outcome' => null,
            'prize_tier_id' => null,
            'payout_minor' => 0,
            'scratch_pct' => 0,
            'scratch_duration' => 0,
            'revealed_at' => null,
            'expires_at' => now()->addDays(7),
            'server_seed_hash' => null,
            'client_seed' => null,
            'nonce' => 0,
            'ip' => $this->faker->ipv4(),
            'ua' => $this->faker->userAgent(),
            'fraud_score' => 0,
            'referrer' => null,
            'session_data' => [],
        ];
    }
}
