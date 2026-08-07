@extends('layouts.admin')

@section('title', 'Landowner #' . $landowner->id)

@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card-panel">
            <div class="panel-header d-flex justify-content-between align-items-center">
                <span>{{ $landowner->land_locality }}</span>
                <span class="status-badge status-{{ $landowner->status }}">{{ $landowner->status }}</span>
            </div>
            <div class="p-4">
                <dl class="row mb-0">
                    <dt class="col-sm-3 text-muted">Landowner</dt>
                    <dd class="col-sm-9">{{ $landowner->name }} · <a href="mailto:{{ $landowner->email }}">{{ $landowner->email }}</a> · {{ $landowner->phone }}</dd>

                    <dt class="col-sm-3 text-muted">Land address</dt>
                    <dd class="col-sm-9">{{ $landowner->land_address }}</dd>

                    <dt class="col-sm-3 text-muted">Locality</dt>
                    <dd class="col-sm-9">{{ $landowner->land_locality }}</dd>

                    <dt class="col-sm-3 text-muted">Size</dt>
                    <dd class="col-sm-9">{{ rtrim(rtrim(number_format($landowner->land_size_katha, 2), '0'), '.') }} katha</dd>

                    <dt class="col-sm-3 text-muted">Road width</dt>
                    <dd class="col-sm-9">{{ rtrim(rtrim(number_format($landowner->road_width_ft, 2), '0'), '.') }} ft</dd>

                    <dt class="col-sm-3 text-muted">Facing</dt>
                    <dd class="col-sm-9">{{ $landowner->land_facing }}</dd>

                    <dt class="col-sm-3 text-muted">Attractive feature</dt>
                    <dd class="col-sm-9" style="white-space: pre-wrap;">{{ $landowner->attractive_feature ?: '—' }}</dd>

                    <dt class="col-sm-3 text-muted">Received</dt>
                    <dd class="col-sm-9">{{ $landowner->created_at->format('d M Y, H:i') }}</dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card-panel">
            <div class="panel-header">Update status</div>
            <div class="p-3">
                <form method="POST" action="{{ route('admin.landowners.update', $landowner) }}">
                    @csrf
                    @method('PUT')
                    <select name="status" class="form-select mb-3">
                        @foreach (\App\Models\Landowner::STATUSES as $s)
                            <option value="{{ $s }}" @selected($landowner->status === $s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-dark w-100">Save</button>
                </form>
                <hr>
                <form method="POST" action="{{ route('admin.landowners.destroy', $landowner) }}" onsubmit="return confirm('Delete this landowner record?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger w-100">Delete</button>
                </form>
                <a class="btn btn-link w-100 mt-2" href="{{ route('admin.landowners.index') }}">← Back to list</a>
            </div>
        </div>
    </div>
</div>
@endsection
