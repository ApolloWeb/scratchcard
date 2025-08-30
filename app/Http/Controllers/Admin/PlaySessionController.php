<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlaySession;
use Illuminate\Http\Request;

class PlaySessionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index()
    {
        return PlaySession::with(['campaign', 'prizeTier', 'batch'])->paginate(25);
    }

    public function show(PlaySession $playSession)
    {
        return $playSession;
    }

    public function update(Request $request, PlaySession $playSession)
    {
        $data = $request->validate([
            'status' => 'sometimes|string',
            'outcome' => 'nullable|string',
            'prize_tier_id' => 'nullable|string|exists:prize_tiers,id',
            'payout_minor' => 'nullable|integer',
            'revealed_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
        ]);

        $playSession->update($data);

        return $playSession;
    }

    public function destroy(PlaySession $playSession)
    {
        $playSession->delete();

        return response(null, 204);
    }
}
