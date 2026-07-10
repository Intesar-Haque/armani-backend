@extends('layouts.admin')

@section('title', 'Enquiries')

@section('content')
<div class="card-panel">
    <div class="panel-header">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name, email, project…" value="{{ $filters['search'] }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All statuses</option>
                    @foreach (\App\Models\Enquiry::STATUSES as $s)
                        <option value="{{ $s }}" @selected($filters['status'] === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-auto">
                <button class="btn btn-sm btn-dark">Filter</button>
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.enquiries.index') }}">Reset</a>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Contact</th>
                    <th>Project</th>
                    <th>Budget</th>
                    <th>Status</th>
                    <th>Received</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($enquiries as $e)
                    <tr>
                        <td>
                            <div>{{ $e->name }}</div>
                            <small class="text-muted">{{ $e->email }} @if ($e->phone) · {{ $e->phone }} @endif</small>
                        </td>
                        <td>{{ $e->project_name ?: '—' }}</td>
                        <td>{{ $e->budget_range ?: '—' }}</td>
                        <td><span class="status-badge status-{{ $e->status }}">{{ $e->status }}</span></td>
                        <td><small class="text-muted">{{ $e->created_at->format('d M Y, H:i') }}</small></td>
                        <td class="text-end"><a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.enquiries.show', $e) }}">Open</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No enquiries found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $enquiries->links() }}</div>
</div>
@endsection
