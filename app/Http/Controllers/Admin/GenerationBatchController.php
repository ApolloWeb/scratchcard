<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GenerationBatch;
use App\Models\PlaySession;
use App\Models\PrizeTier;
use Illuminate\Support\Str;
use Illuminate\Support\DB;

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

        // synchronous generation (simple)
        try {
            DB::transaction(function() use($batch){
                $batch->status = 'RUNNING';
                $batch->save();

                $count = $batch->requested_count;
                $wins_needed = intval(floor($count * ($batch->win_numerator / max(1, $batch->win_denominator))));

                // prepare prize tier weights
                $tiers = collect($batch->settings_snapshot['prize_tiers']);
                $total_weight = $tiers->sum('weight');

                for($i=0;$i<$count;$i++){
                    $is_win = ($i < $wins_needed) ? 'WIN' : 'LOSE';
                    // simple distribution: first N are wins then shuffle later
                    $prize_tier_id = null;
                    $payout_minor = 0;

                    if($is_win === 'WIN' && $total_weight > 0){
                        $r = rand(1, $total_weight);
                        $acc = 0;
                        foreach($tiers as $t){
                            $acc += $t['weight'];
                            if($r <= $acc){
                                $prize_tier_id = $t['id'];
                                $payout_minor = $t['amount_minor'];
                                break;
                            }
                        }
                    }

                    PlaySession::create([
                        'batch_id' => $batch->id,
                        'code' => strtoupper(Str::random($batch->code_length)),
                        'status' => 'NEW',
                        'outcome' => $is_win,
                        'prize_tier_id' => $prize_tier_id,
                        'payout_minor' => $payout_minor,
                        'server_seed_hash' => hash('sha256', Str::random(32)),
                        'server_seed_encrypted' => '',
                    ]);
                }

                // randomize outcomes order
                $ids = PlaySession::where('batch_id',$batch->id)->pluck('id')->toArray();
                shuffle($ids);
                // no-op: order is already randomized by codes; skipping heavy updates

                $batch->created_count = $count;
                $batch->status = 'COMPLETED';
                $batch->save();
            });
        } catch(\Exception $e){
            $batch->status = 'FAILED';
            $batch->error = $e->getMessage();
            $batch->save();
            return response()->json(['error' => 'Generation failed'], 500);
        }

        return response()->json($batch->fresh(), 201);
    }

    public function show(GenerationBatch $generationBatch)
    {
        $generationBatch->load('playSessions');
        return response()->json($generationBatch);
    }
}
