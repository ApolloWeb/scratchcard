<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PlaySession;

class PlaySessionController extends Controller
{
    public function index(Request $request)
    {
        $q = PlaySession::query();
        if($request->has('batch_id')){
            $q->where('batch_id', $request->get('batch_id'));
        }
        return response()->json($q->orderBy('created_at','desc')->paginate(50));
    }

    public function show(PlaySession $playSession)
    {
        return response()->json($playSession);
    }

    public function update(Request $request, PlaySession $playSession)
    {
        $data = $request->validate([
            'status' => 'nullable|in:NEW,SCRATCHING,REVEALED,EXPIRED',
            'scratch_pct' => 'nullable|integer|min:0|max:100',
            'revealed_at' => 'nullable|date',
        ]);

        $playSession->update($data);

        return response()->json($playSession);
    }
}
