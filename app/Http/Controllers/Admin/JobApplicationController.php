<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobPosting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class JobApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $query = JobApplication::query()->with('jobPosting')->latest();

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }
        if ($jobPostingFilter = $request->string('job_posting_id')->toString()) {
            $jobPostingFilter === '0'
                ? $query->whereNull('job_posting_id')
                : $query->where('job_posting_id', (int) $jobPostingFilter);
        }
        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return view('admin.job-applications.index', [
            'applications' => $query->paginate(20)->withQueryString(),
            'jobs' => JobPosting::orderBy('title')->get(),
            'filters' => [
                'status' => $request->string('status')->toString(),
                'job_posting_id' => $request->string('job_posting_id')->toString(),
                'search' => $request->string('search')->toString(),
            ],
        ]);
    }

    public function show(JobApplication $jobApplication): View
    {
        return view('admin.job-applications.show', ['application' => $jobApplication]);
    }

    public function update(Request $request, JobApplication $jobApplication): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:' . implode(',', JobApplication::STATUSES)],
        ]);

        $jobApplication->update($data);

        return redirect()
            ->route('admin.job-applications.show', $jobApplication)
            ->with('status', 'Application updated.');
    }

    public function destroy(JobApplication $jobApplication): RedirectResponse
    {
        if ($jobApplication->cv_path) {
            Storage::disk('local')->delete($jobApplication->cv_path);
        }
        $jobApplication->delete();

        return redirect()
            ->route('admin.job-applications.index')
            ->with('status', 'Application deleted.');
    }

    public function downloadCv(JobApplication $jobApplication)
    {
        abort_unless((bool) $jobApplication->cv_path, 404);

        return Storage::disk('local')->download(
            $jobApplication->cv_path,
            $jobApplication->cv_original_filename ?: "cv-{$jobApplication->name}.pdf",
        );
    }
}
