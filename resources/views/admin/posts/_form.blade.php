<form method="POST" action="{{ $action }}" enctype="multipart/form-data">
    @csrf
    @if ($method !== 'POST') @method($method) @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="row g-3">
        <div class="col-md-8">
            <label class="form-label">Title</label>
            <input class="form-control" name="title" value="{{ old('title', $post->title ?? '') }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select class="form-select" name="status">
                @foreach (\App\Models\Post::STATUSES as $s)
                    <option value="{{ $s }}" @selected(old('status', $post->status ?? 'draft') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12">
            <label class="form-label">Excerpt</label>
            <input class="form-control" name="excerpt" maxlength="255" value="{{ old('excerpt', $post->excerpt ?? '') }}">
        </div>
        <div class="col-12">
            <label class="form-label">Body</label>
            <textarea class="form-control" name="body" rows="8" required>{{ old('body', $post->body ?? '') }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label">Featured image</label>
            <input class="form-control" type="file" name="featured_image" accept="image/*">
            @if (! empty($post?->featured_image_path))
                <div class="mt-2"><img src="{{ $post->imageUrl() }}" alt="" style="max-height: 100px; border-radius: 6px;"></div>
            @endif
        </div>
        <div class="col-md-6">
            <label class="form-label">YouTube URL</label>
            <input class="form-control" type="url" name="youtube_url" value="{{ old('youtube_url', $post->youtube_url ?? '') }}" placeholder="https://youtube.com/watch?v=…">
        </div>
        <div class="col-md-6">
            <label class="form-label">Event date <small class="text-muted">(optional)</small></label>
            <input class="form-control" type="date" name="event_date" value="{{ old('event_date', $post?->event_date?->toDateString()) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Location <small class="text-muted">(optional)</small></label>
            <input class="form-control" name="location" value="{{ old('location', $post->location ?? '') }}">
        </div>
    </div>
    <div class="mt-4 d-flex gap-2">
        <button class="btn btn-dark">Save</button>
        <a class="btn btn-outline-secondary" href="{{ route('admin.posts.index') }}">Cancel</a>
    </div>
</form>
