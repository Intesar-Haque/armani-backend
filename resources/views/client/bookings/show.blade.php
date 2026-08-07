@extends('layouts.client')

@section('title', 'Booking ' . $booking->reference)

@section('content')
<div class="mb-3">
    <a href="{{ route('client.dashboard') }}" class="text-decoration-none text-muted">← Back to all bookings</a>
</div>

<div class="card-panel mb-3">
    <div class="panel-header d-flex justify-content-between align-items-center">
        <span>Booking · <code>{{ $booking->reference }}</code></span>
        <span class="status-badge status-{{ $booking->status }}">{{ $booking->status }}</span>
    </div>
    <div class="p-4">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="text-muted small">Project</div>
                <div class="fs-6">{{ $booking->project->name }}</div>
                @if($booking->project->location)<small class="text-muted">{{ $booking->project->location }}</small>@endif
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Unit</div>
                <div class="fs-6">{{ $booking->unit_number ?: '—' }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Booked on</div>
                <div class="fs-6">{{ $booking->booking_date->format('d M Y') }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Payment plan</div>
                <div class="fs-6">{{ $booking->planLabel() }}</div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Property price</div>
                <div class="fs-6">৳ {{ number_format((float) $booking->property_price, 2) }}</div>
                @if ((float) $booking->surcharge_amount > 0)
                    <small class="text-muted">+ ৳ {{ number_format((float) $booking->surcharge_amount, 2) }} additional</small>
                @elseif ((float) $booking->discount_amount > 0)
                    <small class="text-success">− ৳ {{ number_format((float) $booking->discount_amount, 2) }} full-payment discount</small>
                @endif
            </div>
            <div class="col-md-4">
                <div class="text-muted small">Down payment</div>
                <div class="fs-6">৳ {{ number_format((float) $booking->down_payment, 2) }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="label">Total payable</div>
            <div class="value">৳ {{ number_format((float) $booking->total_amount, 2) }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="label">Paid to date</div>
            <div class="value text-success">৳ {{ number_format($totalPaid, 2) }}</div>
            <div class="text-muted small mt-1">{{ $paidInstallments->count() }} installment(s) paid</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="label">Upcoming</div>
            <div class="value">৳ {{ number_format($upcomingInstallments->sum(fn($i)=>(float)$i->amount_due), 2) }}</div>
            <div class="text-muted small mt-1">{{ $upcomingInstallments->count() }} installment(s)</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="label">Overdue</div>
            <div class="value text-danger">৳ {{ number_format($overdueInstallments->sum(fn($i)=>(float)$i->amount_due), 2) }}</div>
            <div class="text-muted small mt-1">{{ $overdueInstallments->count() }} installment(s)</div>
        </div>
    </div>
</div>

<div class="card-panel mb-3">
    <div class="panel-header">Installment schedule</div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr><th>#</th><th>Description</th><th>Due date</th><th>Amount</th><th>Paid</th><th>Status</th></tr>
            </thead>
            <tbody>
                @foreach ($booking->installments as $i)
                    @php $s = $i->statusLabel(); @endphp
                    <tr>
                        <td>{{ $i->sequence }}</td>
                        <td>{{ $i->label }}</td>
                        <td>{{ $i->due_date?->format('d M Y') }}</td>
                        <td>৳ {{ number_format((float) $i->amount_due, 2) }}</td>
                        <td>
                            @if ($i->isPaid())
                                ৳ {{ number_format((float) $i->paid_amount, 2) }}
                                @if($i->paid_at)<div class="text-muted small">on {{ $i->paid_at->format('d M Y') }}</div>@endif
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td><span class="status-badge status-{{ $s }}">{{ $s }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card-panel h-100">
            <div class="panel-header">Money receipts</div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>Receipt</th><th>Amount</th><th>Date</th><th></th></tr></thead>
                    <tbody>
                        @forelse ($receipts as $r)
                            <tr>
                                <td>{{ $r->title }}</td>
                                <td>{{ $r->amount ? '৳ '.number_format((float)$r->amount,2) : '—' }}</td>
                                <td><small class="text-muted">{{ $r->issued_at?->format('d M Y') ?? $r->created_at->format('d M Y') }}</small></td>
                                <td class="text-end">
                                    @if ($r->file_path)
                                        <a href="{{ route('client.bookings.documents.download', [$booking, $r]) }}" class="btn btn-sm btn-outline-dark">Download</a>
                                    @else
                                        <a href="{{ route('client.bookings.receipt', [$booking, $r]) }}" target="_blank" class="btn btn-sm btn-outline-dark">View</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">No receipts yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card-panel h-100">
            <div class="panel-header">Deeds & other documents</div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>Type</th><th>Title</th><th>Issued</th><th></th></tr></thead>
                    <tbody>
                        @php $others = $deeds->concat($otherDocs); @endphp
                        @forelse ($others as $d)
                            <tr>
                                <td><span class="status-badge status-active">{{ $d->type }}</span></td>
                                <td>{{ $d->title }}</td>
                                <td><small class="text-muted">{{ $d->issued_at?->format('d M Y') ?? $d->created_at->format('d M Y') }}</small></td>
                                <td class="text-end">
                                    @if ($d->file_path)
                                        <a href="{{ route('client.bookings.documents.download', [$booking, $d]) }}" class="btn btn-sm btn-outline-dark">Download</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">No deeds or documents yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
