@extends('layouts.client')

@section('title', 'My Bookings')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="label">Total booked</div>
            <div class="value">৳ {{ number_format($totalValue, 2) }}</div>
            <div class="text-muted small mt-1">Across {{ $bookings->count() }} booking(s)</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="label">Paid to date</div>
            <div class="value text-success">৳ {{ number_format($totalPaid, 2) }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="label">Balance</div>
            <div class="value text-danger">৳ {{ number_format($totalBalance, 2) }}</div>
        </div>
    </div>
</div>

<div class="card-panel">
    <div class="panel-header">Your bookings</div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Project</th>
                    <th>Total value</th>
                    <th>Paid</th>
                    <th>Balance</th>
                    <th>Booked on</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bookings as $b)
                    @php
                        $paid = (float) ($b->total_paid ?? 0);
                        $balance = (float) $b->total_amount - $paid;
                    @endphp
                    <tr>
                        <td><code>{{ $b->reference }}</code></td>
                        <td>
                            <div>{{ $b->project->name }}</div>
                            @if($b->unit_number)<small class="text-muted">Unit {{ $b->unit_number }}</small>@endif
                        </td>
                        <td>৳ {{ number_format((float) $b->total_amount, 2) }}</td>
                        <td class="text-success">৳ {{ number_format($paid, 2) }}</td>
                        <td class="text-danger">৳ {{ number_format($balance, 2) }}</td>
                        <td><small class="text-muted">{{ $b->booking_date->format('d M Y') }}</small></td>
                        <td><span class="status-badge status-{{ $b->status }}">{{ $b->status }}</span></td>
                        <td class="text-end"><a class="btn btn-sm btn-dark" href="{{ route('client.bookings.show', $b) }}">View details</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-5">You have no bookings yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
