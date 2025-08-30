<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PlaySessionFactory extends Factory
{
    public function definition()
    {
        return [
            'id' => (string) Str::ulid(),
            'batch_id' => (string) Str::ulid(),
            'code' => strtoupper($this->faker->bothify('????-????')),
            'status' => 'NEW',
            'outcome' => $this->faker->randomElement(['WIN','LOSE']),
            'prize_tier_id' => null,
            'payout_minor' => 0,
            'scratch_pct' => 0,
            'scratch_duration' => 0,
            'server_seed_hash' => Str::random(64),
            'server_seed_encrypted' => '',
            'client_seed' => null,
            'nonce' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
