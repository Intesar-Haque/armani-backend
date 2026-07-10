@extends('layouts.admin')

@section('title', 'Site Visit #' . $visit->id)

@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card-panel">
            <div class="panel-header d-flex justify-content-between align-items-center">
                <span>Site Visit from {{ $visit->name }}</span>
                <span class="status-badge status-{{ $visit->status }}">{{ $visit->status }}</span>
            </div>
            <div class="p-4">
                <dl class="row mb-0">
                    <dt class="col-sm-3 text-muted">Name</dt>
                    <dd class="col-sm-9">{{ $visit->name }}</dd>

                    <dt class="col-sm-3 text-muted">Email</dt>
                    <dd class="col-sm-9"><a href="mailto:{{ $visit->email }}">{{ $visit->email }}</a></dd>

                    <dt class="col-sm-3 text-muted">Phone</dt>
                    <dd class="col-sm-9">{{ $visit->phone ?: '—' }}</dd>

                    <dt class="col-sm-3 text-muted">Project</dt>
                    <dd class="col-sm-9">{{ $visit->project_name ?: '—' }} <small class="text-muted">({{ $visit->project_slug ?: 'no slug' }})</small></dd>

                    <dt class="col-sm-3 text-muted">Preferred visit</dt>
                    <dd class="col-sm-9">{{ $visit->preferred_visit_at?->format('d M Y, H:i') ?: 'Flexible' }}</dd>

                    <dt class="col-sm-3 text-muted">Party size</dt>
                    <dd class="col-sm-9">{{ $visit->party_size ?: '—' }}</dd>

                    <dt class="col-sm-3 text-muted">Received</dt>
                    <dd class="col-sm-9">{{ $visit->created_at->format('d M Y, H:i') }}</dd>

                    <dt class="col-sm-3 text-muted">Message</dt>
                    <dd class="col-sm-9">{{ $visit->message ?: '—' }}</dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card-panel">
            <div class="panel-header">Update status</div>
            <div class="p-3">
                <form method="POST" action="{{ route('admin.site-visits.update', $visit) }}">
                    @csrf
                    @method('PUT')
                    <select name="status" class="form-select mb-3">
                        @foreach (\App\Models\SiteVisit::STATUSES as $s)
                            <option value="{{ $s }}" @selected($visit->status === $s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-dark w-100">Save</button>
                </form>
                <hr>
                <form method="POST" action="{{ route('admin.site-visits.destroy', $visit) }}" onsubmit="return confirm('Delete this site visit?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger w-100">Delete</button>
                </form>
                <a class="btn btn-link w-100 mt-2" href="{{ route('admin.site-visits.index') }}">← Back to list</a>
            </div>
        </div>
    </div>
</div>
@endsection
