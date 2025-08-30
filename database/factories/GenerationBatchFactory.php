<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class GenerationBatchFactory extends Factory
{
    public function definition()
    {
        return [
            'id' => (string) Str::ulid(),
            'name' => $this->faker->word(),
            'requested_count' => $this->faker->numberBetween(10,1000),
            'created_count' => 0,
            'status' => 'PENDING',
            'win_numerator' => 1,
            'win_denominator' => 10,
            'code_length' => 8,
            'settings_snapshot' => json_encode(['prize_tiers' => []]),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
