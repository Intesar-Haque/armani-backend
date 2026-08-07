@extends('layouts.admin')

@section('title', 'New Post')

@section('content')
<div class="card-panel">
    <div class="panel-header">Create post</div>
    <div class="p-4">
        @include('admin.posts._form', ['action' => route('admin.posts.store'), 'method' => 'POST', 'post' => null])
    </div>
</div>
@endsection
