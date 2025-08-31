<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlaySession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\ValidationException;

class PlayController extends Controller
{
    /**
     * Update scratch progress and reveal outcome if threshold met
     */
    public function scratch(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'scratch_pct' => 'required|integer|min:0|max:100',
            'client_seed' => 'nullable|string|max:255',
        ]);
        
        $playSession = PlaySession::findOrFail($id);
        
        // Check if already revealed or expired
        if ($playSession->status === 'REVEALED') {
            return response()->json(['error' => 'Ticket already revealed'], 400);
        }
        
        if ($playSession->status === 'EXPIRED') {
            return response()->json(['error' => 'Ticket has expired'], 400);
        }
        
        // Check if ticket has expired
        if ($playSession->expires_at && $playSession->expires_at->isPast()) {
            $playSession->update(['status' => 'EXPIRED']);
            return response()->json(['error' => 'Ticket has expired'], 400);
        }
        
        $scratchPct = $request->integer('scratch_pct');
        $clientSeed = $request->string('client_seed');
        
        // Update scratch progress
        $updateData = [
            'scratch_pct' => max($playSession->scratch_pct, $scratchPct),
            'status' => $scratchPct >= 65 ? 'REVEALED' : 'SCRATCHING',
        ];
        
        // Set client seed if provided and not already set
        if ($clientSeed && !$playSession->client_seed) {
            $updateData['client_seed'] = $clientSeed;
        }
        
        // If revealing, set revealed_at timestamp
        if ($scratchPct >= 65) {
            $updateData['revealed_at'] = now();
        }
        
        // Track IP and user agent
        $updateData['ip'] = $request->ip();
        $updateData['ua'] = $request->userAgent();
        
        $playSession->update($updateData);
        
        $response = [
            'status' => $playSession->status,
            'scratch_pct' => $playSession->scratch_pct,
            'play_id' => $playSession->id,
            'id' => $playSession->id,  // Also include id for consistency
            'box_symbols' => $playSession->box_symbols,
            'winning_symbol' => $playSession->winning_symbol,
        ];
        
        // Include outcome and prize if revealed
        if ($playSession->status === 'REVEALED') {
            $response['outcome'] = $playSession->outcome;
            $response['revealed_at'] = $playSession->revealed_at;
            
            if ($playSession->outcome === 'WIN') {
                $response['prize'] = [
                    'label' => $playSession->prizeTier?->label,
                    'amount_minor' => $playSession->payout_minor,
                ];
                $response['payout_minor'] = $playSession->payout_minor;
            }
        }
        
        return response()->json($response);
    }
    
    /**
     * Verify the outcome by revealing the server seed
     */
    public function verify(string $id): JsonResponse
    {
        $playSession = PlaySession::findOrFail($id);
        
        // Only allow verification after reveal
        if ($playSession->status !== 'REVEALED') {
            return response()->json(['error' => 'Ticket must be revealed before verification'], 400);
        }
        
        // Decrypt server seed
        $serverSeed = null;
        if ($playSession->server_seed_encrypted) {
            try {
                $serverSeed = Crypt::decryptString($playSession->server_seed_encrypted);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Unable to decrypt server seed'], 500);
            }
        }
        
        return response()->json([
            'server_seed' => $serverSeed,
            'server_seed_hash' => $playSession->server_seed_hash,
            'client_seed' => $playSession->client_seed,
            'nonce' => $playSession->nonce,
            'outcome' => $playSession->outcome,
            'verification_instructions' => 'Hash the server_seed with SHA-256 to verify it matches server_seed_hash. Then combine with client_seed and nonce to reproduce the random value.',
        ]);
    }
}
