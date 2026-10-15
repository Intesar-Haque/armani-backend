@extends('layouts.admin')

@section('title', 'Clients')

@section('content')
<div class="card-panel mb-4">
    <div class="panel-header">Register a new client</div>
    <div class="p-4">
        @if ($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif
        <form method="POST" action="{{ route('admin.clients.store') }}" class="row g-3 align-items-end">
            @csrf
            <div class="col-md-4">
                <label class="form-label">Name</label>
                <input class="form-control" name="client_name" value="{{ old('client_name') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Email</label>
                <input class="form-control" type="email" name="client_email" value="{{ old('client_email') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Phone</label>
                <input class="form-control" name="client_phone" value="{{ old('client_phone') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">NID</label>
                <input class="form-control" name="nid" value="{{ old('nid') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Father's name</label>
                <input class="form-control" name="father_name" value="{{ old('father_name') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Mother's name</label>
                <input class="form-control" name="mother_name" value="{{ old('mother_name') }}">
            </div>
            <div class="col-md-10">
                <label class="form-label">Address</label>
                <textarea class="form-control" name="address" rows="2">{{ old('address') }}</textarea>
            </div>
            <div class="col-md-2">
                <button class="btn btn-dark w-100">Register</button>
            </div>
        </form>
        <small class="text-muted d-block mt-2">A temporary password is generated and shown once after registering — share it with the client.</small>
    </div>
</div>

<div class="card-panel">
    <div class="panel-header">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <input class="form-control form-control-sm" name="q" value="{{ $search }}" placeholder="Search name, email, phone, NID">
            </div>
            <div class="col-md-auto">
                <button class="btn btn-sm btn-dark">Search</button>
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.clients.index') }}">Reset</a>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr><th>Name</th><th>Email</th><th>Phone</th><th>NID</th><th>Bookings</th><th>Registered</th></tr>
            </thead>
            <tbody>
                @forelse ($clients as $c)
                    <tr>
                        <td>
                            <div>{{ $c->name }}</div>
                            @if ($c->father_name)
                                <small class="text-muted d-block">Father: {{ $c->father_name }}</small>
                            @endif
                            @if ($c->mother_name)
                                <small class="text-muted d-block">Mother: {{ $c->mother_name }}</small>
                            @endif
                            @if ($c->address)
                                <small class="text-muted d-block">{{ $c->address }}</small>
                            @endif
                        </td>
                        <td>{{ $c->email }}</td>
                        <td>{{ $c->phone ?: '—' }}</td>
                        <td>{{ $c->nid ?: '—' }}</td>
                        <td>{{ $c->bookings_count }}</td>
                        <td>{{ $c->created_at->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No clients yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $clients->links() }}</div>
</div>
@endsection
