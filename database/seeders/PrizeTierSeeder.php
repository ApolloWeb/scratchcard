<?php

namespace Database\Seeders;

use App\Models\PrizeTier;
use Illuminate\Database\Seeder;

class PrizeTierSeeder extends Seeder
{
    public function run(): void
    {
        PrizeTier::factory()->count(6)->create();
    }
}
