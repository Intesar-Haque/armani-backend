<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobPosting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class JobPostingController extends Controller
{
    public function index(Request $request): View
    {
        $query = JobPosting::query()->withCount('applications')->latest();

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }
        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        return view('admin.jobs.index', [
            'jobs' => $query->paginate(20)->withQueryString(),
            'filters' => [
                'status' => $request->string('status')->toString(),
                'search' => $request->string('search')->toString(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.jobs.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $job = JobPosting::create($data);

        return redirect()->route('admin.jobs.show', $job)->with('status', 'Job posting created.');
    }

    public function show(JobPosting $job): View
    {
        return view('admin.jobs.show', [
            'job' => $job,
            'applications' => $job->applications()->latest()->paginate(20),
        ]);
    }

    public function edit(JobPosting $job): View
    {
        return view('admin.jobs.edit', ['job' => $job]);
    }

    public function update(Request $request, JobPosting $job): RedirectResponse
    {
        $job->update($this->validated($request));

        return redirect()->route('admin.jobs.show', $job)->with('status', 'Job posting updated.');
    }

    public function destroy(JobPosting $job): RedirectResponse
    {
        $job->delete();

        return redirect()->route('admin.jobs.index')->with('status', 'Job posting deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'department' => ['nullable', 'string', 'max:150'],
            'location' => ['nullable', 'string', 'max:150'],
            'employment_type' => ['nullable', 'string', 'max:100'],
            'description' => ['required', 'string'],
            'requirements' => ['nullable', 'string'],
            'status' => ['required', 'in:' . implode(',', JobPosting::STATUSES)],
            'closes_at' => ['nullable', 'date'],
        ]);
    }
}
