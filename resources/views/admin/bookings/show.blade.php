@extends('layouts.admin')

@section('title', 'Booking ' . $booking->reference)

@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card-panel mb-3">
            <div class="panel-header d-flex justify-content-between align-items-center">
                <span>Booking · <code>{{ $booking->reference }}</code></span>
                <span class="status-badge status-{{ $booking->status === 'active' ? 'new' : ($booking->status === 'cancelled' ? 'cancelled' : 'completed') }}">{{ $booking->status }}</span>
            </div>
            <div class="p-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="text-muted small">Client</div>
                        <div>{{ $booking->client->name }}</div>
                        <small class="text-muted">{{ $booking->client->email }}</small>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Project</div>
                        <div>{{ $booking->project->name }}</div>
                        @if($booking->unit_number)<small class="text-muted">Unit {{ $booking->unit_number }}</small>@endif
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Booked on</div>
                        <div>{{ $booking->booking_date->format('d M Y') }}</div>
                    </div>
                    @if ($booking->apartment)
                    <div class="col-md-4">
                        <div class="text-muted small">Apartment</div>
                        <div>{{ $booking->apartment->apartment_number }} · {{ $booking->apartment->floor_label }}</div>
                        <small class="text-muted">{{ $booking->apartment->size_sqft }} sqft · {{ $booking->apartment->facing }} facing · ৳ {{ number_format((float) $booking->apartment->per_sqft_rate) }}/sqft</small>
                    </div>
                    @endif
                    <div class="col-md-4">
                        <div class="text-muted small">Payment plan</div>
                        <div>{{ $booking->planLabel() }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Property price (gross)</div>
                        <div>৳ {{ number_format((float) $booking->property_price, 2) }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Booking money</div>
                        <div>৳ {{ number_format((float) $booking->booking_amount, 2) }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Down payment</div>
                        <div>৳ {{ number_format((float) $booking->down_payment, 2) }}</div>
                    </div>
                    @if ((float) $booking->surcharge_amount > 0)
                    <div class="col-md-4">
                        <div class="text-muted small">Additional ({{ rtrim(rtrim(number_format((float) $booking->surcharge_rate, 2), '0'), '.') }}%)</div>
                        <div>৳ {{ number_format((float) $booking->surcharge_amount, 2) }}</div>
                    </div>
                    @endif
                    @if ((float) $booking->discount_amount > 0)
                    <div class="col-md-4">
                        <div class="text-muted small">Full payment discount</div>
                        <div class="text-success">− ৳ {{ number_format((float) $booking->discount_amount, 2) }}</div>
                    </div>
                    @endif
                    <div class="col-md-4">
                        <div class="text-muted small">Total payable</div>
                        <div class="fs-5">৳ {{ number_format((float) $booking->total_amount, 2) }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Paid to date</div>
                        <div class="fs-5 text-success">৳ {{ number_format($totalPaid, 2) }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Balance</div>
                        <div class="fs-5 text-danger">৳ {{ number_format($balance, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-panel mb-3">
            <div class="panel-header">Installments</div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Label</th>
                            <th>Due</th>
                            <th>Amount</th>
                            <th>Paid</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($booking->installments as $i)
                            <tr>
                                <td>{{ $i->sequence }}</td>
                                <td>{{ $i->label }}</td>
                                <td>{{ $i->due_date?->format('d M Y') }}</td>
                                <td>৳ {{ number_format((float) $i->amount_due, 2) }}</td>
                                <td>৳ {{ number_format((float) $i->paid_amount, 2) }}</td>
                                <td>
                                    @php $s = $i->statusLabel(); @endphp
                                    <span class="status-badge status-{{ $s === 'paid' ? 'completed' : ($s === 'overdue' ? 'cancelled' : 'new') }}">{{ $s }}</span>
                                </td>
                                <td class="text-end">
                                    @if (! $i->isPaid())
                                        <button class="btn btn-sm btn-outline-dark" data-bs-toggle="collapse" data-bs-target="#pay-{{ $i->id }}">Record payment</button>
                                    @endif
                                </td>
                            </tr>
                            @if (! $i->isPaid())
                            <tr class="collapse" id="pay-{{ $i->id }}">
                                <td colspan="7" class="bg-light">
                                    <form method="POST" action="{{ route('admin.bookings.installments.pay', [$booking, $i]) }}" class="row g-2 py-2">
                                        @csrf
                                        <div class="col-md-2"><input class="form-control form-control-sm" type="number" step="0.01" min="0" name="paid_amount" value="{{ $i->amount_due }}" required placeholder="Amount"></div>
                                        <div class="col-md-2"><input class="form-control form-control-sm" type="date" name="paid_at" value="{{ now()->toDateString() }}"></div>
                                        <div class="col-md-2"><input class="form-control form-control-sm" name="payment_method" placeholder="Method (UPI, cheque…)"></div>
                                        <div class="col-md-3"><input class="form-control form-control-sm" name="payment_reference" placeholder="Reference"></div>
                                        <div class="col-md-3"><button class="btn btn-sm btn-dark w-100">Save & email receipt</button></div>
                                    </form>
                                </td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-panel">
            <div class="panel-header d-flex justify-content-between align-items-center">
                <span>Documents · Deeds & Receipts</span>
                <button class="btn btn-sm btn-outline-dark" data-bs-toggle="collapse" data-bs-target="#doc-upload"><i class="bi bi-upload"></i> Upload</button>
            </div>
            <div class="collapse" id="doc-upload">
                <div class="p-3 border-bottom bg-light">
                    <form method="POST" action="{{ route('admin.bookings.documents.store', $booking) }}" enctype="multipart/form-data" class="row g-2">
                        @csrf
                        <div class="col-md-3">
                            <select name="type" class="form-select form-select-sm" required>
                                <option value="deed">Deed</option>
                                <option value="receipt">Receipt</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-3"><input name="title" class="form-control form-control-sm" placeholder="Title" required></div>
                        <div class="col-md-2"><input name="amount" type="number" step="0.01" min="0" class="form-control form-control-sm" placeholder="Amount (if receipt)"></div>
                        <div class="col-md-2"><input name="issued_at" type="date" class="form-control form-control-sm" value="{{ now()->toDateString() }}"></div>
                        <div class="col-md-2"><input name="file" type="file" class="form-control form-control-sm" required></div>
                        <div class="col-12"><button class="btn btn-sm btn-dark">Upload document</button></div>
                    </form>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr><th>Type</th><th>Title</th><th>Amount</th><th>Issued</th><th>File</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($booking->documents as $d)
                            <tr>
                                <td><span class="status-badge status-{{ $d->type === 'deed' ? 'new' : 'completed' }}">{{ $d->type }}</span></td>
                                <td>{{ $d->title }}</td>
                                <td>{{ $d->amount ? '৳ '.number_format((float)$d->amount,2) : '—' }}</td>
                                <td><small class="text-muted">{{ $d->issued_at?->format('d M Y') ?? $d->created_at->format('d M Y') }}</small></td>
                                <td>
                                    @if ($d->file_path)
                                        <a href="{{ route('admin.bookings.documents.download', [$booking, $d]) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Open</a>
                                    @else
                                        <small class="text-muted">System-generated</small>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('admin.bookings.documents.destroy', [$booking, $d]) }}" onsubmit="return confirm('Delete this document?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No documents yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-panel mb-3">
            <div class="panel-header">Booking status</div>
            <div class="p-3">
                <form method="POST" action="{{ route('admin.bookings.update', $booking) }}">
                    @csrf @method('PUT')
                    <select name="status" class="form-select mb-3">
                        @foreach (\App\Models\Booking::STATUSES as $s)
                            <option value="{{ $s }}" @selected($booking->status === $s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                    <label class="form-label small text-muted">Internal notes</label>
                    <textarea name="notes" class="form-control mb-3" rows="4">{{ $booking->notes }}</textarea>
                    <button class="btn btn-dark w-100">Save</button>
                </form>
                <hr>
                <form method="POST" action="{{ route('admin.bookings.destroy', $booking) }}" onsubmit="return confirm('Delete this booking?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger w-100">Delete booking</button>
                </form>
                <a class="btn btn-link w-100 mt-2" href="{{ route('admin.bookings.index') }}">← Back to list</a>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@endsection
