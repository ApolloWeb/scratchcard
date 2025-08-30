<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GenerationBatch;
use App\Models\PlaySession;
use App\Models\PrizeTier;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Jobs\GenerateBatchJob;

class GenerationBatchController extends Controller
{
    public function index()
    {
        return response()->json(GenerationBatch::orderBy('created_at','desc')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'nullable|string',
            'requested_count' => 'required|integer|min:1',
            'win_numerator' => 'required|integer|min:0',
            'win_denominator' => 'required|integer|min:1',
            'code_length' => 'nullable|integer|min:6|max:12',
        ]);

        $code_length = $data['code_length'] ?? 8;

        $prize_tiers = PrizeTier::orderBy('weight','desc')->get()->map(function($t){
            return [
                'id' => $t->id,
                'label' => $t->label,
                'amount_minor' => $t->amount_minor,
                'weight' => $t->weight,
            ];
        })->values()->all();

        $batch = GenerationBatch::create([
            'name' => $data['name'] ?? null,
            'requested_count' => $data['requested_count'],
            'created_count' => 0,
            'status' => 'PENDING',
            'win_numerator' => $data['win_numerator'],
            'win_denominator' => $data['win_denominator'],
            'code_length' => $code_length,
            'settings_snapshot' => ['prize_tiers' => $prize_tiers],
        ]);

        // dispatch to queued job
        GenerateBatchJob::dispatch($batch);

        return response()->json($batch->fresh(), 201);
    }

    public function show(GenerationBatch $generationBatch)
    {
        $generationBatch->load('playSessions');
        return response()->json($generationBatch);
    }

    // Progress endpoint
    public function progress(GenerationBatch $generationBatch)
    {
        return response()->json([
            'id' => $generationBatch->id,
            'status' => $generationBatch->status,
            'requested_count' => $generationBatch->requested_count,
            'created_count' => $generationBatch->created_count,
            'error' => $generationBatch->error,
        ]);
    }
}
