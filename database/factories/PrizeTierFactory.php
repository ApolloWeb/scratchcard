<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PrizeTierFactory extends Factory
{
    public function definition()
    {
        return [
            'id' => (string) Str::ulid(),
            'label' => '£' . $this->faker->randomElement([1,2,5,10,20]),
            'amount_minor' => $this->faker->randomElement([100,200,500,1000,2000]),
            'weight' => $this->faker->numberBetween(1,100),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
