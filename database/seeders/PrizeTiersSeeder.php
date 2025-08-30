<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PrizeTiersSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('prize_tiers')->insert([
            [
                'id' => (string) Str::ulid(),
                'label' => '£1',
                'amount_minor' => 100,
                'weight' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => (string) Str::ulid(),
                'label' => '£2',
                'amount_minor' => 200,
                'weight' => 30,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => (string) Str::ulid(),
                'label' => '£5',
                'amount_minor' => 500,
                'weight' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
