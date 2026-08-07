@extends('layouts.admin')

@section('title', 'Edit Post')

@section('content')
<div class="card-panel">
    <div class="panel-header d-flex justify-content-between align-items-center">
        <span>Edit post</span>
        <form method="POST" action="{{ route('admin.posts.destroy', $post) }}" onsubmit="return confirm('Delete this post?')">
            @csrf
            @method('DELETE')
            <button class="btn btn-sm btn-outline-danger">Delete</button>
        </form>
    </div>
    <div class="p-4">
        @include('admin.posts._form', ['action' => route('admin.posts.update', $post), 'method' => 'PUT', 'post' => $post])
    </div>
</div>
@endsection
