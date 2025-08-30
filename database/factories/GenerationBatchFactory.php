<?php

namespace Database\Factories;

use App\Models\GenerationBatch;
use App\Models\Campaign;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class GenerationBatchFactory extends Factory
{
    protected $model = GenerationBatch::class;

    public function definition()
    {
        return [
            'id' => (string) Str::ulid(),
            'campaign_id' => Campaign::factory(),
            'name' => $this->faker->word(),
            'count' => $this->faker->numberBetween(10, 100),
            'decide_at' => now(),
            'settings_snapshot' => [],
            'generated_by' => null,
            'expires_at' => now()->addDays(30),
        ];
    }
}
