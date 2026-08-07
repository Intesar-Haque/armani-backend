@extends('layouts.admin')

@section('title', 'Posts')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Posts <small class="text-muted">Blog &amp; Events</small></h5>
    <a href="{{ route('admin.posts.create') }}" class="btn btn-dark btn-sm">
        <i class="bi bi-plus"></i> New post
    </a>
</div>
<div class="card-panel">
    <div class="panel-header">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search title…" value="{{ $filters['search'] }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All statuses</option>
                    @foreach (\App\Models\Post::STATUSES as $s)
                        <option value="{{ $s }}" @selected($filters['status'] === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-auto">
                <button class="btn btn-sm btn-dark">Filter</button>
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.posts.index') }}">Reset</a>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr><th>Title</th><th>Event date</th><th>Status</th><th>Published</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($posts as $post)
                    <tr>
                        <td>
                            <div>{{ $post->title }}</div>
                            <small class="text-muted">{{ $post->slug }}</small>
                        </td>
                        <td>{{ $post->event_date?->format('d M Y') ?: '—' }}</td>
                        <td><span class="status-badge status-{{ $post->status === 'published' ? 'completed' : 'new' }}">{{ $post->status }}</span></td>
                        <td>{{ $post->published_at?->format('d M Y') ?: '—' }}</td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.posts.edit', $post) }}">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No posts yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $posts->links() }}</div>
</div>
@endsection
