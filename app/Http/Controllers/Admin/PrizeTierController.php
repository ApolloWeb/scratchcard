<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrizeTier;
use Illuminate\Http\Request;

class PrizeTierController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index()
    {
        return PrizeTier::paginate(20);
    }

    public function show(PrizeTier $prizeTier)
    {
        return $prizeTier;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'label' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount_minor' => 'nullable|integer',
            'weight' => 'nullable|integer',
            'max_wins' => 'nullable|integer',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $prize = PrizeTier::create($data);

        return response($prize, 201);
    }

    public function update(Request $request, PrizeTier $prizeTier)
    {
        $data = $request->validate([
            'label' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'amount_minor' => 'nullable|integer',
            'weight' => 'nullable|integer',
            'max_wins' => 'nullable|integer',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $prizeTier->update($data);

        return $prizeTier;
    }

    public function destroy(PrizeTier $prizeTier)
    {
        $prizeTier->delete();

        return response(null, 204);
    }
}
