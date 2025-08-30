<?php

namespace Database\Factories;

use App\Models\Campaign;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CampaignFactory extends Factory
{
    protected $model = Campaign::class;

    public function definition()
    {
        return [
            'id' => (string) Str::ulid(),
            'name' => $this->faker->company(),
            'description' => $this->faker->sentence(),
            'starts_at' => now()->subDays(rand(1, 10)),
            'expires_at' => now()->addDays(rand(10, 30)),
            'max_plays' => $this->faker->numberBetween(100, 1000),
            'is_active' => true,
            'theme_config' => ['color' => $this->faker->safeColorName()],
            'locale' => 'en',
            'created_by' => null,
        ];
    }
}
