@extends('layouts.admin')

@section('title', 'Projects')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Projects</h5>
    <a href="{{ route('admin.projects.create') }}" class="btn btn-dark btn-sm">
        <i class="bi bi-plus"></i> New project
    </a>
</div>
<div class="card-panel">
    <div class="panel-header">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name, location…" value="{{ $filters['search'] }}">
            </div>
            <div class="col-md-auto">
                <button class="btn btn-sm btn-dark">Filter</button>
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.projects.index') }}">Reset</a>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Location</th>
                    <th>Base price</th>
                    <th>Bookings</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($projects as $p)
                    <tr>
                        <td>
                            <div>{{ $p->name }}</div>
                            <small class="text-muted">{{ $p->slug }}</small>
                        </td>
                        <td>{{ $p->location ?: '—' }}</td>
                        <td>৳ {{ number_format((float) $p->base_price, 2) }}</td>
                        <td>{{ $p->bookings_count }}</td>
                        <td><span class="status-badge status-{{ $p->status === 'active' ? 'new' : 'completed' }}">{{ $p->status }}</span></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.projects.edit', $p) }}">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No projects yet. Add one to start booking.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $projects->links() }}</div>
</div>
@endsection
