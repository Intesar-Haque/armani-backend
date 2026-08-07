@extends('layouts.admin')

@section('title', 'Share Assignments')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Share Assignments</h5>
    <a href="{{ route('admin.share-assignments.create') }}" class="btn btn-dark btn-sm">
        <i class="bi bi-plus"></i> Assign shares
    </a>
</div>
<div class="card-panel">
    <div class="panel-header">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search reference, client…" value="{{ $filters['search'] }}">
            </div>
            <div class="col-md-3">
                <select name="project_id" class="form-select form-select-sm">
                    <option value="">All projects</option>
                    @foreach ($projects as $p)
                        <option value="{{ $p->id }}" @selected($filters['project_id'] === $p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All statuses</option>
                    @foreach (\App\Models\ShareAssignment::STATUSES as $s)
                        <option value="{{ $s }}" @selected($filters['status'] === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-auto">
                <button class="btn btn-sm btn-dark">Filter</button>
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.share-assignments.index') }}">Reset</a>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr><th>Reference</th><th>Project</th><th>Client</th><th>Shares</th><th>Total amount</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($assignments as $a)
                    <tr>
                        <td><a href="{{ route('admin.share-assignments.show', $a) }}"><code>{{ $a->reference }}</code></a></td>
                        <td>{{ $a->project->name }}</td>
                        <td>{{ $a->client->name }}</td>
                        <td>{{ $a->shares_count }}</td>
                        <td>৳ {{ number_format((float) $a->total_amount, 2) }}</td>
                        <td><span class="status-badge status-{{ $a->status === 'active' ? 'completed' : 'cancelled' }}">{{ $a->status }}</span></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.share-assignments.show', $a) }}">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No share assignments yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $assignments->links() }}</div>
</div>
@endsection
