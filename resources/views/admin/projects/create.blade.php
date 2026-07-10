@extends('layouts.admin')

@section('title', 'New Project')

@section('content')
<div class="card-panel">
    <div class="panel-header">Create project</div>
    <div class="p-4">
        @include('admin.projects._form', ['action' => route('admin.projects.store'), 'method' => 'POST', 'project' => null])
    </div>
</div>
@endsection
