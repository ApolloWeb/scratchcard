<?php

namespace Database\Factories;

use App\Models\GameSetting;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class GameSettingFactory extends Factory
{
    protected $model = GameSetting::class;

    public function definition()
    {
        return [
            'id' => (string) Str::ulid(),
            'win_numerator' => 1,
            'win_denominator' => 10,
            'reveal_threshold' => 50,
            'min_scratch_time' => 1000,
            'is_active' => true,
        ];
    }
}
