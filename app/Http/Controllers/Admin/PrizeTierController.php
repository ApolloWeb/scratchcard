<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PrizeTier;
use Illuminate\Support\Str;

class PrizeTierController extends Controller
{
    public function index()
    {
        return response()->json(PrizeTier::orderBy('weight','desc')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'label' => 'required|string',
            'amount_minor' => 'required|integer|min:0',
            'weight' => 'required|integer|min:0',
        ]);

        $tier = PrizeTier::create($data);

        return response()->json($tier, 201);
    }

    public function destroy(PrizeTier $prizeTier)
    {
        $prizeTier->delete();

        return response()->noContent();
    }
}
