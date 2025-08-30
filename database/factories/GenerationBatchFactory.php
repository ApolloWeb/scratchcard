<?php

namespace Database\Factories;

use App\Models\GenerationBatch;
use App\Models\AdminUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class GenerationBatchFactory extends Factory
{
    protected $model = GenerationBatch::class;

    public function definition()
    {
        $admin = AdminUser::inRandomOrder()->first();
        if (! $admin) {
            $admin = AdminUser::factory()->superAdmin()->create([
                'email' => 'admin@example.com',
            ]);
        }

        return [
            'id' => (string) Str::ulid(),
            'name' => $this->faker->word(),
            'count' => $this->faker->numberBetween(10, 100),
            'decide_at' => $this->faker->randomElement(['generation', 'reveal']),
            'settings_snapshot' => [],
            'generated_by' => $admin->id,
            'expires_at' => now()->addDays(30),
        ];
    }
}
