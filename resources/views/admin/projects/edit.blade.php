@extends('layouts.admin')

@section('title', 'Edit Project')

@section('content')
<div class="card-panel">
    <div class="panel-header d-flex justify-content-between align-items-center">
        <span>Edit project</span>
        <form method="POST" action="{{ route('admin.projects.destroy', $project) }}" onsubmit="return confirm('Delete this project? All its bookings will be affected.')">
            @csrf
            @method('DELETE')
            <button class="btn btn-sm btn-outline-danger">Delete</button>
        </form>
    </div>
    <div class="p-4">
        @include('admin.projects._form', ['action' => route('admin.projects.update', $project), 'method' => 'PUT', 'project' => $project])
    </div>
</div>
@endsection
