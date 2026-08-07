@extends('layouts.admin')

@section('title', 'Edit Job Posting')

@section('content')
<div class="card-panel">
    <div class="panel-header d-flex justify-content-between align-items-center">
        <span>Edit job posting</span>
        <form method="POST" action="{{ route('admin.jobs.destroy', $job) }}" onsubmit="return confirm('Delete this job posting? Its applications will remain but lose their job link.')">
            @csrf
            @method('DELETE')
            <button class="btn btn-sm btn-outline-danger">Delete</button>
        </form>
    </div>
    <div class="p-4">
        @include('admin.jobs._form', ['action' => route('admin.jobs.update', $job), 'method' => 'PUT', 'job' => $job])
    </div>
</div>
@endsection
