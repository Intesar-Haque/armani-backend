@extends('layouts.admin')

@section('title', 'Project Directors')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Project Directors</h5>
    <a href="{{ route('admin.project-directors.create') }}" class="btn btn-dark btn-sm">
        <i class="bi bi-plus"></i> Assign director
    </a>
</div>
<div class="card-panel">
    <div class="panel-header">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <select name="project_id" class="form-select form-select-sm">
                    <option value="">All projects</option>
                    @foreach ($projects as $p)
                        <option value="{{ $p->id }}" @selected($filters['project_id'] === $p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-auto">
                <button class="btn btn-sm btn-dark">Filter</button>
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.project-directors.index') }}">Reset</a>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr><th>Project</th><th>Director</th><th>Designation</th><th>Appointed</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($directors as $d)
                    <tr>
                        <td>{{ $d->project->name }}</td>
                        <td>
                            <div>{{ $d->client->name }}</div>
                            <small class="text-muted">{{ $d->client->email }}</small>
                        </td>
                        <td>{{ $d->designation ?: '—' }}</td>
                        <td>{{ $d->appointed_date?->format('d M Y') ?: '—' }}</td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#edit-{{ $d->id }}">Edit</button>
                            <form method="POST" action="{{ route('admin.project-directors.destroy', $d) }}" class="d-inline" onsubmit="return confirm('Remove this director?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Remove</button>
                            </form>
                        </td>
                    </tr>
                    <tr class="collapse" id="edit-{{ $d->id }}">
                        <td colspan="5" class="bg-light">
                            <form method="POST" action="{{ route('admin.project-directors.update', $d) }}" class="row g-2 py-2">
                                @csrf @method('PUT')
                                <div class="col-md-3"><input class="form-control form-control-sm" name="designation" value="{{ $d->designation }}" placeholder="Designation"></div>
                                <div class="col-md-2"><input class="form-control form-control-sm" type="date" name="appointed_date" value="{{ $d->appointed_date?->toDateString() }}"></div>
                                <div class="col-md-4"><input class="form-control form-control-sm" name="notes" value="{{ $d->notes }}" placeholder="Notes"></div>
                                <div class="col-md-3"><button class="btn btn-sm btn-dark w-100">Save</button></div>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No directors assigned yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $directors->links() }}</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@endsection
