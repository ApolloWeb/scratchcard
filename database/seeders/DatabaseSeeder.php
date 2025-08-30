<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test user if not exists
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        $this->call([AdminUserSeeder::class]);

        $this->call([
            CampaignSeeder::class,
            PrizeTierSeeder::class,
            GenerationBatchSeeder::class,
            GameSettingSeeder::class,
            PlaySessionSeeder::class,
        ]);
    }
}
