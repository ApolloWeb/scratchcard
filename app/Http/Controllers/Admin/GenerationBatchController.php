<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GenerationBatch;
use Illuminate\Http\Request;

class GenerationBatchController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index()
    {
        return GenerationBatch::with('campaign')->paginate(20);
    }

    public function show(GenerationBatch $generationBatch)
    {
        return $generationBatch;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'campaign_id' => 'required|string|exists:campaigns,id',
            'name' => 'required|string|max:255',
            'count' => 'nullable|integer',
            'decide_at' => 'nullable|date',
            'settings_snapshot' => 'nullable|array',
            'generated_by' => 'nullable|string',
            'expires_at' => 'nullable|date',
        ]);

        $batch = GenerationBatch::create($data);

        return response($batch, 201);
    }

    public function update(Request $request, GenerationBatch $generationBatch)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'count' => 'nullable|integer',
            'decide_at' => 'nullable|date',
            'settings_snapshot' => 'nullable|array',
            'expires_at' => 'nullable|date',
        ]);

        $generationBatch->update($data);

        return $generationBatch;
    }

    public function destroy(GenerationBatch $generationBatch)
    {
        $generationBatch->delete();

        return response(null, 204);
    }
}
