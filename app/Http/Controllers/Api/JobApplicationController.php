<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobPosting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobApplicationController extends Controller
{
    public function store(Request $request, ?JobPosting $jobPosting = null): JsonResponse
    {
        abort_if($jobPosting && $jobPosting->status !== JobPosting::STATUS_OPEN, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'cover_letter' => ['nullable', 'string', 'max:5000'],
            'cv' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ]);

        $path = $request->file('cv')->store('job-applications', 'local');

        $application = JobApplication::create([
            'job_posting_id' => $jobPosting?->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'cover_letter' => $data['cover_letter'] ?? null,
            'cv_path' => $path,
            'cv_original_filename' => $request->file('cv')->getClientOriginalName(),
        ]);

        return response()->json([
            'message' => 'Application received. Our team will be in touch if there is a match.',
            'id' => $application->id,
        ], 201);
    }
}
