<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateBatchJob;
use App\Models\GenerationBatch;
use App\Models\PlaySession;
use App\Models\PrizeTier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GenerationBatchController extends Controller
{
    /**
     * Generate a new batch of tickets
     */
    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'count' => 'required|integer|min:1|max:100000',
            'win_numerator' => 'required|integer|min:0',
            'win_denominator' => 'required|integer|min:1',
            'code_length' => 'nullable|integer|min:8|max:10',
        ]);
        
        // Validate win ratio
        if ($request->integer('win_numerator') > $request->integer('win_denominator')) {
            return response()->json([
                'error' => 'Win numerator cannot be greater than denominator'
            ], 400);
        }
        
        // Get current prize tiers for snapshot
        $prizeTiers = PrizeTier::all();
        
        if ($prizeTiers->isEmpty()) {
            return response()->json([
                'error' => 'No prize tiers configured. Please create at least one prize tier first.'
            ], 400);
        }
        
        // Create the batch
        $batch = GenerationBatch::create([
            'name' => $request->string('name'),
            'requested_count' => $request->integer('count'),
            'win_numerator' => $request->integer('win_numerator'),
            'win_denominator' => $request->integer('win_denominator'),
            'code_length' => $request->integer('code_length', 8),
            'settings_snapshot' => [
                'prize_tiers' => $prizeTiers->map(function ($tier) {
                    return [
                        'id' => $tier->id,
                        'label' => $tier->label,
                        'amount_minor' => $tier->amount_minor,
                        'weight' => $tier->weight,
                    ];
                })->toArray(),
            ],
        ]);
        
        // Dispatch the generation job
        GenerateBatchJob::dispatch($batch);
        
        return response()->json($batch, 201);
    }
    
    /**
     * Get paginated list of batches
     */
    public function index(Request $request): JsonResponse
    {
        $query = GenerationBatch::query()
            ->withCount(['playSessions as total_tickets'])
            ->withCount(['playSessions as wins' => function ($query) {
                $query->where('outcome', 'WIN');
            }])
            ->orderBy('created_at', 'desc');
        
        $batches = $query->paginate($request->integer('per_page', 15));
        
        return response()->json($batches);
    }
    
    /**
     * Get batch details
     */
    public function show(string $id): JsonResponse
    {
        $batch = GenerationBatch::with(['playSessions' => function ($query) {
            $query->selectRaw('batch_id, count(*) as total, sum(case when outcome = "WIN" then 1 else 0 end) as wins, sum(case when status = "REVEALED" then 1 else 0 end) as revealed')
                  ->groupBy('batch_id');
        }])->findOrFail($id);
        
        $sessionStats = $batch->playSessions->first();
        
        $batchData = $batch->toArray();
        $batchData['stats'] = [
            'total_tickets' => $sessionStats?->total ?? 0,
            'wins' => $sessionStats?->wins ?? 0,
            'revealed' => $sessionStats?->revealed ?? 0,
        ];
        
        return response()->json($batchData);
    }
    
    /**
     * Get tickets for a specific batch
     */
    public function tickets(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'status' => 'nullable|in:NEW,SCRATCHING,REVEALED,EXPIRED',
            'outcome' => 'nullable|in:WIN,LOSE',
            'search' => 'nullable|string|max:10',
        ]);
        
        $batch = GenerationBatch::findOrFail($id);
        
        $query = $batch->playSessions()
            ->with('prizeTier:id,label,amount_minor');
        
        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        
        if ($request->filled('outcome')) {
            $query->where('outcome', $request->string('outcome'));
        }
        
        if ($request->filled('search')) {
            $query->where('code', 'like', '%' . $request->string('search') . '%');
        }
        
        $tickets = $query->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 50));
        
        return response()->json($tickets);
    }
    
    /**
     * Export batch tickets as CSV
     */
    public function export(string $id): Response
    {
        $batch = GenerationBatch::findOrFail($id);
        
        $tickets = $batch->playSessions()
            ->with('prizeTier:id,label,amount_minor')
            ->orderBy('created_at', 'desc')
            ->get();
        
        $csv = "code,link,outcome,prize_label,amount_minor,created_at\n";
        
        foreach ($tickets as $ticket) {
            $link = url("/t/{$ticket->code}");
            $prizeLabel = $ticket->outcome === 'WIN' ? ($ticket->prizeTier?->label ?? '') : '';
            $amountMinor = $ticket->outcome === 'WIN' ? $ticket->payout_minor : 0;
            
            $csv .= sprintf(
                "%s,%s,%s,%s,%d,%s\n",
                $ticket->code,
                $link,
                $ticket->outcome,
                $prizeLabel,
                $amountMinor,
                $ticket->created_at->toISOString()
            );
        }
        
        $filename = "batch_{$batch->id}_tickets_" . now()->format('Y-m-d_H-i-s') . '.csv';
        
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}
