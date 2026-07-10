@extends('layouts.admin')

@section('title', 'Contact Query #' . $query->id)

@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card-panel">
            <div class="panel-header d-flex justify-content-between align-items-center">
                <span>{{ $query->subject }}</span>
                <span class="status-badge status-{{ $query->status }}">{{ $query->status }}</span>
            </div>
            <div class="p-4">
                <dl class="row mb-0">
                    <dt class="col-sm-3 text-muted">From</dt>
                    <dd class="col-sm-9">{{ $query->name }} · <a href="mailto:{{ $query->email }}">{{ $query->email }}</a></dd>

                    <dt class="col-sm-3 text-muted">Phone</dt>
                    <dd class="col-sm-9">{{ $query->phone ?: '—' }}</dd>

                    <dt class="col-sm-3 text-muted">Received</dt>
                    <dd class="col-sm-9">{{ $query->created_at->format('d M Y, H:i') }}</dd>

                    <dt class="col-sm-3 text-muted">Message</dt>
                    <dd class="col-sm-9" style="white-space: pre-wrap;">{{ $query->message }}</dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card-panel">
            <div class="panel-header">Update status</div>
            <div class="p-3">
                <form method="POST" action="{{ route('admin.queries.update', $query) }}">
                    @csrf
                    @method('PUT')
                    <select name="status" class="form-select mb-3">
                        @foreach (\App\Models\ContactQuery::STATUSES as $s)
                            <option value="{{ $s }}" @selected($query->status === $s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-dark w-100">Save</button>
                </form>
                <hr>
                <form method="POST" action="{{ route('admin.queries.destroy', $query) }}" onsubmit="return confirm('Delete this query?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger w-100">Delete</button>
                </form>
                <a class="btn btn-link w-100 mt-2" href="{{ route('admin.queries.index') }}">← Back to list</a>
            </div>
        </div>
    </div>
</div>
@endsection
