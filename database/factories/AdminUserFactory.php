<?php

namespace Database\Factories;

use App\Models\AdminUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AdminUserFactory extends Factory
{
    protected $model = AdminUser::class;

    public function definition()
    {
        return [
            'id' => (string) Str::ulid(),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => 'password', // will be hashed by model cast
            'email_verified_at' => now(),
            'role' => $this->faker->randomElement(['super_admin', 'admin', 'viewer']),
            'is_active' => true,
            'last_login_at' => null,
        ];
    }

    public function superAdmin()
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'super_admin',
            'is_active' => true,
        ]);
    }
}
