<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrizeTier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrizeTierController extends Controller
{
    /**
     * Get all prize tiers
     */
    public function index(): JsonResponse
    {
        $prizeTiers = PrizeTier::orderBy('amount_minor', 'desc')->get();
        
        return response()->json($prizeTiers);
    }
    
    /**
     * Create a new prize tier
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'amount_minor' => 'required|integer|min:0',
            'weight' => 'required|integer|min:1',
        ]);
        
        $prizeTier = PrizeTier::create([
            'label' => $request->string('label'),
            'amount_minor' => $request->integer('amount_minor'),
            'weight' => $request->integer('weight'),
        ]);
        
        return response()->json($prizeTier, 201);
    }
    
    /**
     * Delete a prize tier
     */
    public function destroy(string $id): JsonResponse
    {
        $prizeTier = PrizeTier::findOrFail($id);
        
        // Check if this prize tier is referenced by any play sessions
        $referencedSessions = $prizeTier->playSessions()->count();
        
        if ($referencedSessions > 0) {
            return response()->json([
                'error' => "Cannot delete prize tier. It is referenced by {$referencedSessions} play sessions."
            ], 400);
        }
        
        $prizeTier->delete();
        
        return response()->json(['message' => 'Prize tier deleted successfully']);
    }
}
