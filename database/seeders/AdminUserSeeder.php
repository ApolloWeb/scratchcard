<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AdminUser::factory()->superAdmin()->create([
            'email' => 'admin@example.com',
            'name' => 'Super Admin',
        ]);

        AdminUser::factory()->count(3)->create();
    }
}
