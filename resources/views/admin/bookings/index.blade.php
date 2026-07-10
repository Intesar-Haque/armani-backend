@extends('layouts.admin')

@section('title', 'Bookings')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Bookings</h5>
    <a href="{{ route('admin.bookings.create') }}" class="btn btn-dark btn-sm">
        <i class="bi bi-plus"></i> Book a project
    </a>
</div>
<div class="card-panel">
    <div class="panel-header">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Ref, client, project…" value="{{ $filters['search'] }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All statuses</option>
                    @foreach (\App\Models\Booking::STATUSES as $s)
                        <option value="{{ $s }}" @selected($filters['status'] === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-auto">
                <button class="btn btn-sm btn-dark">Filter</button>
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.bookings.index') }}">Reset</a>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Client</th>
                    <th>Project</th>
                    <th>Total</th>
                    <th>Booked on</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bookings as $b)
                    <tr>
                        <td><code>{{ $b->reference }}</code></td>
                        <td>
                            <div>{{ $b->client->name }}</div>
                            <small class="text-muted">{{ $b->client->email }}</small>
                        </td>
                        <td>
                            <div>{{ $b->project->name }}</div>
                            @if($b->unit_number)<small class="text-muted">Unit {{ $b->unit_number }}</small>@endif
                        </td>
                        <td>৳ {{ number_format((float)$b->total_amount, 2) }}</td>
                        <td><small class="text-muted">{{ $b->booking_date->format('d M Y') }}</small></td>
                        <td><span class="status-badge status-{{ $b->status === 'active' ? 'new' : ($b->status === 'cancelled' ? 'cancelled' : 'completed') }}">{{ $b->status }}</span></td>
                        <td class="text-end"><a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.bookings.show', $b) }}">Open</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No bookings yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $bookings->links() }}</div>
</div>
@endsection
