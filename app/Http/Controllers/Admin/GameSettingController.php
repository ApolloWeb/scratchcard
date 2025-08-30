<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GameSetting;
use Illuminate\Http\Request;

class GameSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index()
    {
        return GameSetting::with('campaign')->paginate(20);
    }

    public function show(GameSetting $gameSetting)
    {
        return $gameSetting;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'campaign_id' => 'required|string|exists:campaigns,id',
            'win_numerator' => 'required|integer',
            'win_denominator' => 'required|integer',
            'reveal_threshold' => 'required|integer',
            'min_scratch_time' => 'required|integer',
            'is_active' => 'sometimes|boolean',
        ]);

        $setting = GameSetting::create($data);

        return response($setting, 201);
    }

    public function update(Request $request, GameSetting $gameSetting)
    {
        $data = $request->validate([
            'win_numerator' => 'sometimes|integer',
            'win_denominator' => 'sometimes|integer',
            'reveal_threshold' => 'sometimes|integer',
            'min_scratch_time' => 'sometimes|integer',
            'is_active' => 'sometimes|boolean',
        ]);

        $gameSetting->update($data);

        return $gameSetting;
    }

    public function destroy(GameSetting $gameSetting)
    {
        $gameSetting->delete();

        return response(null, 204);
    }
}
