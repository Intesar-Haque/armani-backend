@extends('layouts.admin')

@section('title', 'Share Assignment ' . $assignment->reference)

@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card-panel">
            <div class="panel-header d-flex justify-content-between align-items-center">
                <span>Share Assignment · <code>{{ $assignment->reference }}</code></span>
                <span class="status-badge status-{{ $assignment->status === 'active' ? 'completed' : 'cancelled' }}">{{ $assignment->status }}</span>
            </div>
            <div class="p-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="text-muted small">Client</div>
                        <div>{{ $assignment->client->name }}</div>
                        <small class="text-muted">{{ $assignment->client->email }}</small>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Project</div>
                        <div>{{ $assignment->project->name }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Assigned on</div>
                        <div>{{ $assignment->assigned_date->format('d M Y') }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Shares</div>
                        <div>{{ $assignment->shares_count }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Share value</div>
                        <div>৳ {{ number_format((float) $assignment->share_value, 2) }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Total amount</div>
                        <div class="fs-5">৳ {{ number_format((float) $assignment->total_amount, 2) }}</div>
                    </div>
                    @if ($assignment->notes)
                    <div class="col-12">
                        <div class="text-muted small">Notes</div>
                        <div>{{ $assignment->notes }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-panel mb-3">
            <div class="panel-header">Status</div>
            <div class="p-3">
                <form method="POST" action="{{ route('admin.share-assignments.update', $assignment) }}">
                    @csrf @method('PUT')
                    <select name="status" class="form-select mb-3">
                        @foreach (\App\Models\ShareAssignment::STATUSES as $s)
                            <option value="{{ $s }}" @selected($assignment->status === $s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                    <label class="form-label small text-muted">Internal notes</label>
                    <textarea name="notes" class="form-control mb-3" rows="4">{{ $assignment->notes }}</textarea>
                    <button class="btn btn-dark w-100">Save</button>
                </form>
                <hr>
                <form method="POST" action="{{ route('admin.share-assignments.destroy', $assignment) }}" onsubmit="return confirm('Delete this share assignment?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger w-100">Delete assignment</button>
                </form>
                <a class="btn btn-link w-100 mt-2" href="{{ route('admin.share-assignments.index') }}">← Back to list</a>
            </div>
        </div>
    </div>
</div>
@endsection
