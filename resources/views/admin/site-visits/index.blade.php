@extends('layouts.admin')

@section('title', 'Site Visits')

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
                    @foreach (\App\Models\SiteVisit::STATUSES as $s)
                        <option value="{{ $s }}" @selected($filters['status'] === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-auto">
                <button class="btn btn-sm btn-dark">Filter</button>
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.site-visits.index') }}">Reset</a>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Contact</th>
                    <th>Project</th>
                    <th>Preferred Visit</th>
                    <th>Party</th>
                    <th>Status</th>
                    <th>Received</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($visits as $v)
                    <tr>
                        <td>
                            <div>{{ $v->name }}</div>
                            <small class="text-muted">{{ $v->email }} @if ($v->phone) · {{ $v->phone }} @endif</small>
                        </td>
                        <td>{{ $v->project_name ?: '—' }}</td>
                        <td>{{ $v->preferred_visit_at?->format('d M Y, H:i') ?: 'Flexible' }}</td>
                        <td>{{ $v->party_size ?: '—' }}</td>
                        <td><span class="status-badge status-{{ $v->status }}">{{ $v->status }}</span></td>
                        <td><small class="text-muted">{{ $v->created_at->format('d M Y, H:i') }}</small></td>
                        <td class="text-end"><a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.site-visits.show', $v) }}">Open</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No site visits found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $visits->links() }}</div>
</div>
@endsection
