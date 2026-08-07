@extends('layouts.admin')

@section('title', 'New Booking')

@section('content')
<div class="card-panel">
    <div class="panel-header">Assign an apartment to a client</div>
    <div class="p-4">
        @if ($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif
        <form method="POST" action="{{ route('admin.bookings.store') }}">
            @csrf

            <h6 class="text-uppercase text-muted small mb-3">Client</h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Existing client</label>
                    <select name="client_id" class="form-select" id="client-select">
                        <option value="">— New client —</option>
                        @foreach ($clients as $c)
                            <option value="{{ $c->id }}" data-email="{{ $c->email }}" data-name="{{ $c->name }}" data-phone="{{ $c->phone }}" @selected(old('client_id') == $c->id)>{{ $c->name }} · {{ $c->email }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Choose a registered client, or leave blank to create one (login details will be emailed).</small>
                </div>
            </div>
            <div class="row g-3 mt-1" id="new-client-block">
                <div class="col-md-4">
                    <label class="form-label">Client name</label>
                    <input class="form-control" name="client_name" value="{{ old('client_name') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Client email</label>
                    <input class="form-control" type="email" name="client_email" value="{{ old('client_email') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Client phone</label>
                    <input class="form-control" name="client_phone" value="{{ old('client_phone') }}">
                </div>
            </div>

            <hr class="my-4">
            <h6 class="text-uppercase text-muted small mb-3">Apartment</h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Available apartment</label>
                    <select name="apartment_id" class="form-select" id="apartment-select" required>
                        <option value="">Select an apartment</option>
                        @foreach ($apartments->groupBy('floor_label') as $floor => $units)
                            <optgroup label="{{ $floor }}">
                                @foreach ($units as $a)
                                    <option value="{{ $a->id }}"
                                        data-gross="{{ $a->gross_total }}"
                                        @selected(old('apartment_id') == $a->id)>
                                        {{ $a->apartment_number }} · {{ $a->size_sqft }} sqft · {{ $a->facing }} · ৳ {{ number_format((float) $a->gross_total) }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    <small class="text-muted">Only apartments currently marked available are listed. Price shown is the gross total (incl. parking &amp; utility).</small>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Booking date</label>
                    <input class="form-control" type="date" name="booking_date" value="{{ old('booking_date', now()->toDateString()) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">First installment / payment due</label>
                    <input class="form-control" type="date" name="first_installment_date" value="{{ old('first_installment_date', now()->addMonth()->toDateString()) }}" required>
                </div>
            </div>

            <hr class="my-4">
            <h6 class="text-uppercase text-muted small mb-3">Payment</h6>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Booking money received (৳)</label>
                    <input class="form-control" type="number" step="0.01" min="{{ $bookingMoney }}" name="booking_amount" id="booking-amount" value="{{ old('booking_amount', $bookingMoney) }}" required>
                    <small class="text-muted">Standard booking money is ৳ {{ number_format($bookingMoney) }}.</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Down payment (৳)</label>
                    <input class="form-control" type="number" step="0.01" min="0" name="down_payment" id="down-payment" value="{{ old('down_payment', 0) }}">
                    <small class="text-muted">Installments are calculated on the remaining amount.</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Payment plan</label>
                    <select name="payment_plan" class="form-select" id="plan-select" required>
                        @foreach ($plans as $months => $rate)
                            <option value="{{ $months }}" data-rate="{{ $rate }}" @selected(old('payment_plan', '12') == (string) $months)>{{ $months }} months ({{ $rate == 0 ? '0%' : rtrim(rtrim(number_format($rate, 2), '0'), '.') . '% additional' }})</option>
                        @endforeach
                        <option value="full" data-rate="0" @selected(old('payment_plan') === 'full')>Full payment ({{ rtrim(rtrim(number_format($fullPaymentDiscount, 2), '0'), '.') }}% discount)</option>
                    </select>
                </div>
                <div class="col-12">
                    <div class="border rounded p-3 bg-light" id="summary" style="display:none;">
                        <div class="row text-center">
                            <div class="col"><div class="text-muted small">Gross price</div><div class="fw-semibold" id="s-gross">—</div></div>
                            <div class="col"><div class="text-muted small" id="s-adj-label">Additional</div><div class="fw-semibold" id="s-adj">—</div></div>
                            <div class="col"><div class="text-muted small">Total payable</div><div class="fw-semibold" id="s-payable">—</div></div>
                            <div class="col"><div class="text-muted small">Upfront (booking + down)</div><div class="fw-semibold" id="s-upfront">—</div></div>
                            <div class="col"><div class="text-muted small" id="s-monthly-label">Monthly installment</div><div class="fw-semibold" id="s-monthly">—</div></div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea class="form-control" name="notes" rows="3">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-dark">Create booking & email client</button>
                <a class="btn btn-outline-secondary" href="{{ route('admin.bookings.index') }}">Cancel</a>
            </div>
        </form>
    </div>
</div>
<script>
document.getElementById('client-select').addEventListener('change', function () {
    const opt = this.options[this.selectedIndex];
    const block = document.getElementById('new-client-block');
    const name = document.querySelector('[name=client_name]');
    const email = document.querySelector('[name=client_email]');
    const phone = document.querySelector('[name=client_phone]');
    if (this.value) {
        name.value = opt.dataset.name || '';
        email.value = opt.dataset.email || '';
        phone.value = opt.dataset.phone || '';
        block.style.display = 'none';
    } else {
        name.value = ''; email.value = ''; phone.value = '';
        block.style.display = '';
    }
});

const fullDiscountPct = {{ (float) $fullPaymentDiscount }};
const fmt = n => '৳ ' + n.toLocaleString('en-US', { maximumFractionDigits: 0 });

function recalc() {
    const aptOpt = document.getElementById('apartment-select').selectedOptions[0];
    const summary = document.getElementById('summary');
    if (!aptOpt || !aptOpt.dataset.gross) { summary.style.display = 'none'; return; }

    const gross = parseFloat(aptOpt.dataset.gross);
    const booking = parseFloat(document.getElementById('booking-amount').value) || 0;
    const down = parseFloat(document.getElementById('down-payment').value) || 0;
    const planEl = document.getElementById('plan-select');
    const plan = planEl.value;
    const upfront = booking + down;

    let adj, payable, monthly, adjLabel, monthlyLabel;
    if (plan === 'full') {
        adj = -(gross * fullDiscountPct / 100);
        payable = gross + adj;
        monthly = Math.max(0, payable - upfront);
        adjLabel = 'Discount (' + fullDiscountPct + '%)';
        monthlyLabel = 'One-time balance due';
    } else {
        const rate = parseFloat(planEl.selectedOptions[0].dataset.rate) || 0;
        const remaining = Math.max(0, gross - upfront);
        adj = remaining * rate / 100;
        payable = gross + adj;
        monthly = (remaining + adj) / parseInt(plan);
        adjLabel = 'Additional (' + rate + '%)';
        monthlyLabel = 'Monthly installment × ' + plan;
    }

    document.getElementById('s-gross').textContent = fmt(gross);
    document.getElementById('s-adj-label').textContent = adjLabel;
    document.getElementById('s-adj').textContent = fmt(adj);
    document.getElementById('s-payable').textContent = fmt(payable);
    document.getElementById('s-upfront').textContent = fmt(upfront);
    document.getElementById('s-monthly-label').textContent = monthlyLabel;
    document.getElementById('s-monthly').textContent = fmt(monthly);
    summary.style.display = '';
}

['apartment-select', 'booking-amount', 'down-payment', 'plan-select'].forEach(id => {
    document.getElementById(id).addEventListener('input', recalc);
    document.getElementById(id).addEventListener('change', recalc);
});
recalc();
</script>
@endsection
