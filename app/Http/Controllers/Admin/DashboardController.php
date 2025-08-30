<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GenerationBatch;
use App\Models\PlaySession;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * Get dashboard summary statistics
     */
    public function summary(): JsonResponse
    {
        $stats = [
            'batches' => GenerationBatch::count(),
            'tickets' => PlaySession::count(),
            'wins' => PlaySession::where('outcome', 'WIN')->count(),
            'loses' => PlaySession::where('outcome', 'LOSE')->count(),
        ];
        
        $recentBatches = GenerationBatch::with(['playSessions' => function ($query) {
            $query->selectRaw('batch_id, count(*) as total, sum(case when outcome = "WIN" then 1 else 0 end) as wins')
                  ->groupBy('batch_id');
        }])
        ->orderBy('created_at', 'desc')
        ->take(5)
        ->get()
        ->map(function ($batch) {
            $sessionStats = $batch->playSessions->first();
            return [
                'id' => $batch->id,
                'name' => $batch->name,
                'status' => $batch->status,
                'requested_count' => $batch->requested_count,
                'created_count' => $batch->created_count,
                'total_tickets' => $sessionStats?->total ?? 0,
                'wins' => $sessionStats?->wins ?? 0,
                'created_at' => $batch->created_at,
            ];
        });
        
        return response()->json([
            'stats' => $stats,
            'recent_batches' => $recentBatches,
        ]);
    }
}
