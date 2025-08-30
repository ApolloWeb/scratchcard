<?php

namespace Database\Factories;

use App\Models\PrizeTier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PrizeTierFactory extends Factory
{
    protected $model = PrizeTier::class;

    public function definition()
    {
        return [
            'id' => (string) Str::ulid(),
            'label' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'amount_minor' => $this->faker->numberBetween(0, 10000),
            'weight' => $this->faker->numberBetween(1, 100),
            'max_wins' => $this->faker->numberBetween(1, 100),
            'current_wins' => 0,
            'is_active' => true,
            'sort_order' => 1,
        ];
    }
}
