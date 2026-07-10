@extends('layouts.admin')

@section('title', 'Enquiry #' . $enquiry->id)

@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card-panel">
            <div class="panel-header d-flex justify-content-between align-items-center">
                <span>Enquiry from {{ $enquiry->name }}</span>
                <span class="status-badge status-{{ $enquiry->status }}">{{ $enquiry->status }}</span>
            </div>
            <div class="p-4">
                <dl class="row mb-0">
                    <dt class="col-sm-3 text-muted">Name</dt>
                    <dd class="col-sm-9">{{ $enquiry->name }}</dd>

                    <dt class="col-sm-3 text-muted">Email</dt>
                    <dd class="col-sm-9"><a href="mailto:{{ $enquiry->email }}">{{ $enquiry->email }}</a></dd>

                    <dt class="col-sm-3 text-muted">Phone</dt>
                    <dd class="col-sm-9">{{ $enquiry->phone ?: '—' }}</dd>

                    <dt class="col-sm-3 text-muted">Project</dt>
                    <dd class="col-sm-9">{{ $enquiry->project_name ?: '—' }} <small class="text-muted">({{ $enquiry->project_slug ?: 'no slug' }})</small></dd>

                    <dt class="col-sm-3 text-muted">Budget range</dt>
                    <dd class="col-sm-9">{{ $enquiry->budget_range ?: '—' }}</dd>

                    <dt class="col-sm-3 text-muted">Received</dt>
                    <dd class="col-sm-9">{{ $enquiry->created_at->format('d M Y, H:i') }}</dd>

                    <dt class="col-sm-3 text-muted">Message</dt>
                    <dd class="col-sm-9">{{ $enquiry->message ?: '—' }}</dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card-panel">
            <div class="panel-header">Update status</div>
            <div class="p-3">
                <form method="POST" action="{{ route('admin.enquiries.update', $enquiry) }}">
                    @csrf
                    @method('PUT')
                    <select name="status" class="form-select mb-3">
                        @foreach (\App\Models\Enquiry::STATUSES as $s)
                            <option value="{{ $s }}" @selected($enquiry->status === $s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-dark w-100">Save</button>
                </form>
                <hr>
                <form method="POST" action="{{ route('admin.enquiries.destroy', $enquiry) }}" onsubmit="return confirm('Delete this enquiry?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger w-100">Delete</button>
                </form>
                <a class="btn btn-link w-100 mt-2" href="{{ route('admin.enquiries.index') }}">← Back to list</a>
            </div>
        </div>
    </div>
</div>
@endsection
