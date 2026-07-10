@extends('layouts.admin')

@section('title', 'Contact Queries')

@section('content')
<div class="card-panel">
    <div class="panel-header">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name, email, subject…" value="{{ $filters['search'] }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All statuses</option>
                    @foreach (\App\Models\ContactQuery::STATUSES as $s)
                        <option value="{{ $s }}" @selected($filters['status'] === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-auto">
                <button class="btn btn-sm btn-dark">Filter</button>
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.queries.index') }}">Reset</a>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Contact</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Received</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($queries as $q)
                    <tr>
                        <td>
                            <div>{{ $q->name }}</div>
                            <small class="text-muted">{{ $q->email }}</small>
                        </td>
                        <td>{{ $q->subject }}</td>
                        <td><span class="status-badge status-{{ $q->status }}">{{ $q->status }}</span></td>
                        <td><small class="text-muted">{{ $q->created_at->format('d M Y, H:i') }}</small></td>
                        <td class="text-end"><a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.queries.show', $q) }}">Open</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No queries found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $queries->links() }}</div>
</div>
@endsection
