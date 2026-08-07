@extends('layouts.admin')

@section('title', 'Job Postings')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Job Postings</h5>
    <a href="{{ route('admin.jobs.create') }}" class="btn btn-dark btn-sm">
        <i class="bi bi-plus"></i> New job posting
    </a>
</div>
<div class="card-panel">
    <div class="panel-header">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search title, department, location…" value="{{ $filters['search'] }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All statuses</option>
                    @foreach (\App\Models\JobPosting::STATUSES as $s)
                        <option value="{{ $s }}" @selected($filters['status'] === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-auto">
                <button class="btn btn-sm btn-dark">Filter</button>
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.jobs.index') }}">Reset</a>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Department</th>
                    <th>Location</th>
                    <th>Applications</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($jobs as $job)
                    <tr>
                        <td>
                            <a href="{{ route('admin.jobs.show', $job) }}">{{ $job->title }}</a>
                            <div><small class="text-muted">{{ $job->slug }}</small></div>
                        </td>
                        <td>{{ $job->department ?: '—' }}</td>
                        <td>{{ $job->location ?: '—' }}</td>
                        <td>{{ $job->applications_count }}</td>
                        <td><span class="status-badge status-{{ $job->status === 'open' ? 'new' : 'cancelled' }}">{{ $job->status }}</span></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.jobs.edit', $job) }}">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No job postings yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $jobs->links() }}</div>
</div>
@endsection
