@extends('layouts.admin')

@section('title', 'Assign Shares')

@section('content')
<div class="card-panel">
    <div class="panel-header">Assign project shares to a client</div>
    <div class="p-4">
        @if ($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif
        <form method="POST" action="{{ route('admin.share-assignments.store') }}">
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
                    <small class="text-muted">Choose a registered client, or leave blank to create one (login details will be emailed on first booking/assignment).</small>
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
            <h6 class="text-uppercase text-muted small mb-3">Shares</h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Project</label>
                    <select name="project_id" class="form-select" id="project-select" required>
                        <option value="">Select a project</option>
                        @foreach ($projects as $p)
                            <option value="{{ $p->id }}" data-remaining="{{ $p->sharesRemaining() }}" data-value="{{ $p->share_value }}" @selected(old('project_id') == $p->id)>
                                {{ $p->name }} · ৳ {{ number_format((float) $p->share_value, 2) }}/share · {{ $p->sharesRemaining() }} remaining
                            </option>
                        @endforeach
                    </select>
                    @if ($projects->isEmpty())
                        <small class="text-danger">No projects have shares configured yet. Set share value &amp; total shares on a project first.</small>
                    @endif
                </div>
                <div class="col-md-3">
                    <label class="form-label">Shares count</label>
                    <input class="form-control" type="number" min="1" name="shares_count" id="shares-count" value="{{ old('shares_count', 1) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Assigned date</label>
                    <input class="form-control" type="date" name="assigned_date" value="{{ old('assigned_date', now()->toDateString()) }}" required>
                </div>
                <div class="col-12">
                    <div class="border rounded p-3 bg-light" id="summary" style="display:none;">
                        <div class="row text-center">
                            <div class="col"><div class="text-muted small">Share value</div><div class="fw-semibold" id="s-value">—</div></div>
                            <div class="col"><div class="text-muted small">Shares</div><div class="fw-semibold" id="s-count">—</div></div>
                            <div class="col"><div class="text-muted small">Total amount</div><div class="fw-semibold" id="s-total">—</div></div>
                            <div class="col"><div class="text-muted small">Remaining after</div><div class="fw-semibold" id="s-remaining">—</div></div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea class="form-control" name="notes" rows="3">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-dark">Assign shares</button>
                <a class="btn btn-outline-secondary" href="{{ route('admin.share-assignments.index') }}">Cancel</a>
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

const fmt = n => '৳ ' + n.toLocaleString('en-US', { maximumFractionDigits: 2 });

function recalc() {
    const projOpt = document.getElementById('project-select').selectedOptions[0];
    const summary = document.getElementById('summary');
    if (!projOpt || !projOpt.value) { summary.style.display = 'none'; return; }

    const value = parseFloat(projOpt.dataset.value) || 0;
    const remaining = parseInt(projOpt.dataset.remaining) || 0;
    const count = parseInt(document.getElementById('shares-count').value) || 0;

    document.getElementById('s-value').textContent = fmt(value);
    document.getElementById('s-count').textContent = count;
    document.getElementById('s-total').textContent = fmt(value * count);
    document.getElementById('s-remaining').textContent = remaining - count;
    summary.style.display = '';
}

['project-select', 'shares-count'].forEach(id => {
    document.getElementById(id).addEventListener('input', recalc);
    document.getElementById(id).addEventListener('change', recalc);
});
recalc();
</script>
@endsection
