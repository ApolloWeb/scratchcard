<?php

namespace Database\Factories;

use App\Models\GameSetting;
use App\Models\Campaign;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class GameSettingFactory extends Factory
{
    protected $model = GameSetting::class;

    public function definition()
    {
        return [
            'id' => (string) Str::ulid(),
            'campaign_id' => Campaign::factory(),
            'win_numerator' => 1,
            'win_denominator' => 10,
            'reveal_threshold' => 50,
            'min_scratch_time' => 1000,
            'is_active' => true,
        ];
    }
}
