<form method="POST" action="{{ $action }}">
    @csrf
    @if ($method !== 'POST') @method($method) @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Job title</label>
            <input class="form-control" name="title" value="{{ old('title', $job->title ?? '') }}" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Department</label>
            <input class="form-control" name="department" value="{{ old('department', $job->department ?? '') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Location</label>
            <input class="form-control" name="location" value="{{ old('location', $job->location ?? '') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Employment type</label>
            <input class="form-control" name="employment_type" value="{{ old('employment_type', $job->employment_type ?? '') }}" placeholder="Full-time, Contract…">
        </div>
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select class="form-select" name="status">
                @foreach (\App\Models\JobPosting::STATUSES as $s)
                    <option value="{{ $s }}" @selected(old('status', $job->status ?? 'open') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Closes on</label>
            <input class="form-control" type="date" name="closes_at" value="{{ old('closes_at', $job?->closes_at?->toDateString()) }}">
        </div>
        <div class="col-12">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" rows="5" required>{{ old('description', $job->description ?? '') }}</textarea>
        </div>
        <div class="col-12">
            <label class="form-label">Requirements</label>
            <textarea class="form-control" name="requirements" rows="4">{{ old('requirements', $job->requirements ?? '') }}</textarea>
        </div>
    </div>
    <div class="mt-4 d-flex gap-2">
        <button class="btn btn-dark">Save</button>
        <a class="btn btn-outline-secondary" href="{{ route('admin.jobs.index') }}">Cancel</a>
    </div>
</form>
