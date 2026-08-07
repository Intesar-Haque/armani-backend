@extends('layouts.admin')

@section('title', 'Job Applications')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Job Applications</h5>
</div>
<div class="card-panel">
    <div class="panel-header">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name, email…" value="{{ $filters['search'] }}">
            </div>
            <div class="col-md-3">
                <select name="job_posting_id" class="form-select form-select-sm">
                    <option value="">All jobs</option>
                    <option value="0" @selected($filters['job_posting_id'] === '0')>Generic (no job selected)</option>
                    @foreach ($jobs as $j)
                        <option value="{{ $j->id }}" @selected($filters['job_posting_id'] === (string) $j->id)>{{ $j->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All statuses</option>
                    @foreach (\App\Models\JobApplication::STATUSES as $s)
                        <option value="{{ $s }}" @selected($filters['status'] === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-auto">
                <button class="btn btn-sm btn-dark">Filter</button>
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.job-applications.index') }}">Reset</a>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr><th>Name</th><th>Job</th><th>Email</th><th>Applied</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($applications as $a)
                    <tr>
                        <td>{{ $a->name }}</td>
                        <td>{{ $a->jobPosting->title ?? 'Generic application' }}</td>
                        <td>{{ $a->email }}</td>
                        <td><small class="text-muted">{{ $a->created_at->format('d M Y') }}</small></td>
                        <td><span class="status-badge status-{{ $a->status === 'new' ? 'new' : ($a->status === 'rejected' ? 'cancelled' : 'completed') }}">{{ $a->status }}</span></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.job-applications.show', $a) }}">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No applications yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $applications->links() }}</div>
</div>
@endsection
