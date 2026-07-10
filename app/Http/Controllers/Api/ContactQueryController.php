<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactQueryController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $query = ContactQuery::create($data);

        return response()->json([
            'message' => 'Thanks — we have received your message and will be in touch shortly.',
            'id' => $query->id,
        ], 201);
    }
}
