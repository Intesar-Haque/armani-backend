<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SiteVisit;
use App\Services\MetaConversionsApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SiteVisitController extends Controller
{
    public function store(Request $request, MetaConversionsApi $meta): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'project_slug' => ['nullable', 'string', 'max:255'],
            'project_name' => ['nullable', 'string', 'max:255'],
            'preferred_visit_at' => ['nullable', 'date', 'after_or_equal:today'],
            'party_size' => ['nullable', 'integer', 'min:1', 'max:50'],
            'message' => ['nullable', 'string', 'max:5000'],
        ]);

        $visit = SiteVisit::create($data);

        // Validation passed and the lead is saved, so this is a real lead:
        // report it to Meta with the event_id the website already used.
        $meta->send($request, 'Schedule', $data);

        return response()->json([
            'message' => 'Site visit request received. We will confirm shortly.',
            'id' => $visit->id,
        ], 201);
    }
}
