@extends('layouts.admin')

@section('title', $job->title)

@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card-panel mb-3">
            <div class="panel-header d-flex justify-content-between align-items-center">
                <span>{{ $job->title }}</span>
                <span class="status-badge status-{{ $job->status === 'open' ? 'new' : 'cancelled' }}">{{ $job->status }}</span>
            </div>
            <div class="p-4">
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="text-muted small">Department</div>
                        <div>{{ $job->department ?: '—' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Location</div>
                        <div>{{ $job->location ?: '—' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Employment type</div>
                        <div>{{ $job->employment_type ?: '—' }}</div>
                    </div>
                </div>
                <h6 class="text-uppercase text-muted small">Description</h6>
                <p style="white-space: pre-line;">{{ $job->description }}</p>
                @if ($job->requirements)
                    <h6 class="text-uppercase text-muted small">Requirements</h6>
                    <p style="white-space: pre-line;">{{ $job->requirements }}</p>
                @endif
            </div>
        </div>

        <div class="card-panel">
            <div class="panel-header">Applications</div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr><th>Name</th><th>Email</th><th>Phone</th><th>Applied</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($applications as $a)
                            <tr>
                                <td>{{ $a->name }}</td>
                                <td>{{ $a->email }}</td>
                                <td>{{ $a->phone ?: '—' }}</td>
                                <td><small class="text-muted">{{ $a->created_at->format('d M Y') }}</small></td>
                                <td><span class="status-badge status-{{ $a->status === 'new' ? 'new' : ($a->status === 'rejected' ? 'cancelled' : 'completed') }}">{{ $a->status }}</span></td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.job-applications.show', $a) }}">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No applications for this posting yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3">{{ $applications->links() }}</div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-panel">
            <div class="panel-header">Manage</div>
            <div class="p-3">
                <a class="btn btn-outline-secondary w-100 mb-2" href="{{ route('admin.jobs.edit', $job) }}">Edit posting</a>
                <a class="btn btn-link w-100" href="{{ route('admin.jobs.index') }}">← Back to list</a>
            </div>
        </div>
    </div>
</div>
@endsection
