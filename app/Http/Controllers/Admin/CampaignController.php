<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CampaignController extends Controller
{
    // campaigns removed; keep controller to avoid route errors

    public function index()
    {
        return response()->json(['message' => 'Campaigns removed'], Response::HTTP_NOT_FOUND);
    }

    public function show($id)
    {
        return response()->json(['message' => 'Campaigns removed'], Response::HTTP_NOT_FOUND);
    }

    public function store(Request $request)
    {
        return response()->json(['message' => 'Campaigns removed'], Response::HTTP_NOT_FOUND);
    }

    public function update(Request $request, $id)
    {
        return response()->json(['message' => 'Campaigns removed'], Response::HTTP_NOT_FOUND);
    }

    public function destroy($id)
    {
        return response()->json(['message' => 'Campaigns removed'], Response::HTTP_NOT_FOUND);
    }
}
