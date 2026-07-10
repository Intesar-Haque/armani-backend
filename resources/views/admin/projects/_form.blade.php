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
            <label class="form-label">Project name</label>
            <input class="form-control" name="name" value="{{ old('name', $project->name ?? '') }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Location</label>
            <input class="form-control" name="location" value="{{ old('location', $project->location ?? '') }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Base price (৳)</label>
            <input class="form-control" type="number" step="0.01" min="0" name="base_price" value="{{ old('base_price', $project->base_price ?? '0') }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Status</label>
            <select class="form-select" name="status">
                @foreach (\App\Models\Project::STATUSES as $s)
                    <option value="{{ $s }}" @selected(old('status', $project->status ?? 'active') === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" rows="4">{{ old('description', $project->description ?? '') }}</textarea>
        </div>
    </div>
    <div class="mt-4 d-flex gap-2">
        <button class="btn btn-dark">Save</button>
        <a class="btn btn-outline-secondary" href="{{ route('admin.projects.index') }}">Cancel</a>
    </div>
</form>
