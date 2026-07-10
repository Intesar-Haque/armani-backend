@extends('layouts.admin')

@section('title', 'New Booking')

@section('content')
<div class="card-panel">
    <div class="panel-header">Book a project for a client</div>
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
                    <small class="text-muted">Choose an existing client, or leave blank to create one.</small>
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
            <h6 class="text-uppercase text-muted small mb-3">Booking</h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Project</label>
                    <select name="project_id" class="form-select" required>
                        <option value="">Select a project</option>
                        @foreach ($projects as $p)
                            <option value="{{ $p->id }}" @selected(old('project_id') == $p->id)>{{ $p->name }} @if($p->location) · {{ $p->location }} @endif</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Unit / villa no.</label>
                    <input class="form-control" name="unit_number" value="{{ old('unit_number') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Booking date</label>
                    <input class="form-control" type="date" name="booking_date" value="{{ old('booking_date', now()->toDateString()) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Total amount (৳)</label>
                    <input class="form-control" type="number" step="0.01" min="0" name="total_amount" value="{{ old('total_amount') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Booking amount received (৳)</label>
                    <input class="form-control" type="number" step="0.01" min="0" name="booking_amount" value="{{ old('booking_amount') }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label"># Installments</label>
                    <input class="form-control" type="number" min="0" max="120" name="installment_count" value="{{ old('installment_count', 12) }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">First installment</label>
                    <input class="form-control" type="date" name="first_installment_date" value="{{ old('first_installment_date', now()->addMonth()->toDateString()) }}">
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
        block.style.display = '';
    }
});
</script>
@endsection
