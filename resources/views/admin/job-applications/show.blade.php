@extends('layouts.admin')

@section('title', 'Application · ' . $application->name)

@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card-panel">
            <div class="panel-header d-flex justify-content-between align-items-center">
                <span>{{ $application->name }}</span>
                <span class="status-badge status-{{ $application->status === 'new' ? 'new' : ($application->status === 'rejected' ? 'cancelled' : 'completed') }}">{{ $application->status }}</span>
            </div>
            <div class="p-4">
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="text-muted small">Applied for</div>
                        <div>{{ $application->jobPosting->title ?? 'Generic application' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Email</div>
                        <div>{{ $application->email }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Phone</div>
                        <div>{{ $application->phone ?: '—' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Applied on</div>
                        <div>{{ $application->created_at->format('d M Y') }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">CV</div>
                        <div><a href="{{ route('admin.job-applications.cv', $application) }}" target="_blank">Download CV</a></div>
                    </div>
                </div>
                @if ($application->cover_letter)
                    <h6 class="text-uppercase text-muted small">Cover letter</h6>
                    <p style="white-space: pre-line;">{{ $application->cover_letter }}</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-panel">
            <div class="panel-header">Manage</div>
            <div class="p-3">
                <form method="POST" action="{{ route('admin.job-applications.update', $application) }}">
                    @csrf @method('PUT')
                    <select name="status" class="form-select mb-3">
                        @foreach (\App\Models\JobApplication::STATUSES as $s)
                            <option value="{{ $s }}" @selected($application->status === $s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-dark w-100">Save</button>
                </form>
                <hr>
                <form method="POST" action="{{ route('admin.job-applications.destroy', $application) }}" onsubmit="return confirm('Delete this application?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger w-100">Delete application</button>
                </form>
                <a class="btn btn-link w-100 mt-2" href="{{ route('admin.job-applications.index') }}">← Back to list</a>
            </div>
        </div>
    </div>
</div>
@endsection
