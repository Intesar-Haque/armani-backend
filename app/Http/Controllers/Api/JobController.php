<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobPosting;
use Illuminate\Http\JsonResponse;

class JobController extends Controller
{
    public function index(): JsonResponse
    {
        $jobs = JobPosting::query()
            ->open()
            ->latest()
            ->get(['id', 'title', 'slug', 'department', 'location', 'employment_type', 'closes_at']);

        return response()->json(['data' => $jobs]);
    }

    public function show(JobPosting $jobPosting): JsonResponse
    {
        abort_unless($jobPosting->status === JobPosting::STATUS_OPEN, 404);

        return response()->json(['data' => $jobPosting]);
    }
}
