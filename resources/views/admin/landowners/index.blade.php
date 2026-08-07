@extends('layouts.admin')

@section('title', 'Landowners')

@section('content')
<div class="card-panel">
    <div class="panel-header">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name, email, phone, locality…" value="{{ $filters['search'] }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All statuses</option>
                    @foreach (\App\Models\Landowner::STATUSES as $s)
                        <option value="{{ $s }}" @selected($filters['status'] === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-auto">
                <button class="btn btn-sm btn-dark">Filter</button>
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.landowners.index') }}">Reset</a>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Landowner</th>
                    <th>Locality</th>
                    <th>Size (katha)</th>
                    <th>Status</th>
                    <th>Received</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($landowners as $landowner)
                    <tr>
                        <td>
                            <div>{{ $landowner->name }}</div>
                            <small class="text-muted">{{ $landowner->phone }}</small>
                        </td>
                        <td>{{ $landowner->land_locality }}</td>
                        <td>{{ rtrim(rtrim(number_format($landowner->land_size_katha, 2), '0'), '.') }}</td>
                        <td><span class="status-badge status-{{ $landowner->status }}">{{ $landowner->status }}</span></td>
                        <td><small class="text-muted">{{ $landowner->created_at->format('d M Y, H:i') }}</small></td>
                        <td class="text-end"><a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.landowners.show', $landowner) }}">Open</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No landowners found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $landowners->links() }}</div>
</div>
@endsection
