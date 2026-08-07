@extends('layouts.admin')

@section('title', 'Apartments')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Apartments</h5>
    <div class="d-flex gap-3">
        <span class="text-success">Available: {{ $counts['available'] }}</span>
        <span class="text-warning">Booked: {{ $counts['booked'] }}</span>
        <span class="text-danger">Sold: {{ $counts['sold'] }}</span>
    </div>
</div>

@if (session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif

<div class="card-panel mb-3">
    <div class="p-3">
        <form method="GET" class="row g-2">
            <div class="col-md-3">
                <select name="project_id" class="form-select form-select-sm">
                    <option value="">All projects</option>
                    @foreach ($projects as $p)
                        <option value="{{ $p->id }}" @selected($filters['project_id'] == $p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All statuses</option>
                    @foreach (\App\Models\Apartment::STATUSES as $s)
                        <option value="{{ $s }}" @selected($filters['status'] === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <input class="form-control form-control-sm" name="search" value="{{ $filters['search'] }}" placeholder="Apartment no. (e.g. 4-A)">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-sm btn-dark">Filter</button>
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.apartments.index') }}">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card-panel">
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead>
                <tr>
                    <th>Floor</th>
                    <th>Apartment</th>
                    <th>Size</th>
                    <th>Facing</th>
                    <th>Rate/sqft</th>
                    <th>Apartment price</th>
                    <th>Gross total</th>
                    <th>Status</th>
                    <th>Client</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($apartments as $a)
                    @php $booking = $a->bookings->first(); @endphp
                    <tr>
                        <td>{{ $a->floor_label }}</td>
                        <td><strong>{{ $a->apartment_number }}</strong></td>
                        <td>{{ $a->size_sqft }} sqft</td>
                        <td>{{ $a->facing }}</td>
                        <td>৳ {{ number_format((float) $a->per_sqft_rate) }}</td>
                        <td>৳ {{ number_format((float) $a->apartment_price) }}</td>
                        <td>৳ {{ number_format((float) $a->gross_total) }}</td>
                        <td>
                            <span class="status-badge status-{{ $a->status === 'available' ? 'completed' : ($a->status === 'booked' ? 'new' : 'cancelled') }}">{{ $a->status }}</span>
                        </td>
                        <td>
                            @if ($booking)
                                <a href="{{ route('admin.bookings.show', $booking) }}">{{ $booking->client->name }}</a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('admin.apartments.update', $a) }}" class="d-flex gap-1 justify-content-end">
                                @csrf @method('PUT')
                                <select name="status" class="form-select form-select-sm" style="width:auto;">
                                    @foreach (\App\Models\Apartment::STATUSES as $s)
                                        <option value="{{ $s }}" @selected($a->status === $s)>{{ ucfirst($s) }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-sm btn-outline-dark">Save</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-center text-muted py-4">No apartments found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">
        {{ $apartments->links() }}
    </div>
</div>
@endsection
