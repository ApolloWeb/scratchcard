<?php

namespace Database\Factories;

use App\Models\PlaySession;
use App\Models\GenerationBatch;
use App\Models\PrizeTier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PlaySessionFactory extends Factory
{
    protected $model = PlaySession::class;

    public function definition()
    {
        $batch = GenerationBatch::inRandomOrder()->first() ?? GenerationBatch::factory()->create();

        return [
            'id' => (string) Str::ulid(),
            'batch_id' => $batch->id,
            'code' => strtoupper($this->faker->bothify('???-####')),
            'masked_token' => (string) Str::uuid(),
            'status' => $this->faker->randomElement(['NEW', 'SCRATCHING', 'REVEALED', 'EXPIRED']),
            'outcome' => $this->faker->optional()->randomElement(['WIN', 'LOSE']),
            'prize_tier_id' => PrizeTier::inRandomOrder()->first()?->id,
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
