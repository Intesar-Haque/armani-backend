@extends('layouts.admin')

@section('title', 'New Job Posting')

@section('content')
<div class="card-panel">
    <div class="panel-header">Create job posting</div>
    <div class="p-4">
        @include('admin.jobs._form', ['action' => route('admin.jobs.store'), 'method' => 'POST', 'job' => null])
    </div>
</div>
@endsection
