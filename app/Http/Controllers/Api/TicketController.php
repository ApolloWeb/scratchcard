<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlaySession;
use Illuminate\Http\JsonResponse;

class TicketController extends Controller
{
    /**
     * Get ticket information by code
     */
    public function show(string $code): JsonResponse
    {
        $playSession = PlaySession::where('code', $code)->first();
        
        if (!$playSession) {
            return response()->json(['error' => 'Ticket not found'], 404);
        }
        
        // Check if ticket has expired
        if ($playSession->expires_at && $playSession->expires_at->isPast()) {
            $playSession->update(['status' => 'EXPIRED']);
        }
        
        return response()->json([
            'play_session' => [
                'play_id' => $playSession->id,
                'id' => $playSession->id,  // Also include id for consistency
                'status' => $playSession->status,
                'scratch_pct' => $playSession->scratch_pct,
                'box_symbols' => $playSession->box_symbols, // Already decoded by Laravel cast
                'winning_symbol' => $playSession->winning_symbol,
                'expires_at' => $playSession->expires_at,
                'outcome' => $playSession->status === 'REVEALED' ? $playSession->outcome : null,
                'revealed_at' => $playSession->revealed_at,
                'payout_minor' => $playSession->payout_minor, // Include payout for winning tickets
            ],
            'prize' => $playSession->status === 'REVEALED' && $playSession->outcome === 'WIN' ? [
                'label' => $playSession->prizeTier?->label,
                'amount_minor' => $playSession->payout_minor,
            ] : null,
            'provably_fair' => [
                'server_seed_hash' => $playSession->server_seed_hash,
                'nonce' => $playSession->nonce,
                'client_seed' => $playSession->client_seed,
            ],
        ]);
    }
}
