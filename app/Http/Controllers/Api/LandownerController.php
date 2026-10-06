<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Landowner;
use App\Services\MetaConversionsApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LandownerController extends Controller
{
    public function store(Request $request, MetaConversionsApi $meta): JsonResponse
    {
        $data = $request->validate([
            'land_locality' => ['required', 'string', 'max:255'],
            'land_address' => ['required', 'string', 'max:1000'],
            'land_size_katha' => ['required', 'numeric', 'min:0'],
            'road_width_ft' => ['required', 'numeric', 'min:0'],
            'land_facing' => ['required', 'string', 'max:100'],
            'attractive_feature' => ['nullable', 'string', 'max:2000'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
        ]);

        $landowner = Landowner::create($data);

        // Validation passed and the lead is saved, so this is a real lead:
        // report it to Meta with the event_id the website already used.
        $meta->send($request, 'Lead', $data);

        return response()->json([
            'message' => 'Thank you. Our team will review your land details and get in touch shortly.',
            'id' => $landowner->id,
        ], 201);
    }
}
