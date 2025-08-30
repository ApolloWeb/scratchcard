<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index()
    {
        return Campaign::withCount(['prizeTiers', 'generationBatches', 'playSessions'])->paginate(20);
    }

    public function show(Campaign $campaign)
    {
        return $campaign->load('prizeTiers', 'generationBatches', 'gameSetting');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
            'max_plays' => 'nullable|integer',
            'is_active' => 'sometimes|boolean',
            'theme_config' => 'nullable|array',
            'locale' => 'nullable|string',
            'created_by' => 'nullable|string',
        ]);

        $campaign = Campaign::create($data);

        return response($campaign, 201);
    }

    public function update(Request $request, Campaign $campaign)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
            'max_plays' => 'nullable|integer',
            'is_active' => 'sometimes|boolean',
            'theme_config' => 'nullable|array',
            'locale' => 'nullable|string',
        ]);

        $campaign->update($data);

        return $campaign;
    }

    public function destroy(Campaign $campaign)
    {
        $campaign->delete();

        return response(null, 204);
    }
}
