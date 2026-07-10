@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="row g-3">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="label">Projects</div>
            <div class="value">{{ $projectsTotal }}</div>
            <div class="sub">Active portfolio</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="label">Bookings</div>
            <div class="value">{{ $bookingsTotal }}</div>
            <div class="sub">{{ $clientsTotal }} clients</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="label">Collected</div>
            <div class="value">৳ {{ number_format($revenueTotal, 0) }}</div>
            <div class="sub">Across all installments</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="label">Enquiries</div>
            <div class="value">{{ $enquiriesTotal }}</div>
            <div class="sub">{{ $enquiriesNew }} new</div>
        </div>
    </div>
</div>

<div class="row g-3 mt-2">
    <div class="col-12">
        <div class="card-panel">
            <div class="panel-header d-flex justify-content-between align-items-center">
                <span>Recent bookings</span>
                <a class="small text-decoration-none" href="{{ route('admin.bookings.index') }}">View all →</a>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr><th>Ref</th><th>Client</th><th>Project</th><th>Total</th><th>Date</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($recentBookings as $b)
                            <tr>
                                <td><code>{{ $b->reference }}</code></td>
                                <td>{{ $b->client->name }}<br><small class="text-muted">{{ $b->client->email }}</small></td>
                                <td>{{ $b->project->name }}</td>
                                <td>৳ {{ number_format((float)$b->total_amount, 2) }}</td>
                                <td><small class="text-muted">{{ $b->booking_date->format('d M Y') }}</small></td>
                                <td><a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.bookings.show', $b) }}">Open</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No bookings yet. <a href="{{ route('admin.bookings.create') }}">Create one</a>.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-2">
    <div class="col-lg-6">
        <div class="card-panel">
            <div class="panel-header d-flex justify-content-between align-items-center">
                <span>Recent enquiries</span>
                <a class="small text-decoration-none" href="{{ route('admin.enquiries.index') }}">View all →</a>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Project</th>
                            <th>When</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentEnquiries as $e)
                            <tr>
                                <td>
                                    <div>{{ $e->name }}</div>
                                    <small class="text-muted">{{ $e->email }}</small>
                                </td>
                                <td>{{ $e->project_name ?: '—' }}</td>
                                <td><small class="text-muted">{{ $e->created_at->diffForHumans() }}</small></td>
                                <td><a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.enquiries.show', $e) }}">View</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">No enquiries yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card-panel">
            <div class="panel-header d-flex justify-content-between align-items-center">
                <span>Recent site visits</span>
                <a class="small text-decoration-none" href="{{ route('admin.site-visits.index') }}">View all →</a>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Project</th>
                            <th>Visit</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentVisits as $v)
                            <tr>
                                <td>
                                    <div>{{ $v->name }}</div>
                                    <small class="text-muted">{{ $v->email }}</small>
                                </td>
                                <td>{{ $v->project_name ?: '—' }}</td>
                                <td><small class="text-muted">{{ $v->preferred_visit_at?->format('d M, H:i') ?: 'TBD' }}</small></td>
                                <td><a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.site-visits.show', $v) }}">View</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">No site visits yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
