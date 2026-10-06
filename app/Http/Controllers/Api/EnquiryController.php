<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Services\MetaConversionsApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnquiryController extends Controller
{
    public function store(Request $request, MetaConversionsApi $meta): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'project_slug' => ['nullable', 'string', 'max:255'],
            'project_name' => ['nullable', 'string', 'max:255'],
            'budget_range' => ['nullable', 'string', 'max:100'],
            'message' => ['nullable', 'string', 'max:5000'],
        ]);

        $enquiry = Enquiry::create($data);

        // Validation passed and the lead is saved, so this is a real lead:
        // report it to Meta with the event_id the website already used.
        $meta->send($request, 'Lead', $data);

        return response()->json([
            'message' => 'Enquiry received. Our team will be in touch shortly.',
            'id' => $enquiry->id,
        ], 201);
    }
}
