<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\JsonResponse;

class PostController extends Controller
{
    public function index(): JsonResponse
    {
        $posts = Post::query()
            ->published()
            ->orderByDesc('published_at')
            ->paginate(12);

        $posts->getCollection()->transform(fn (Post $post) => $this->present($post));

        return response()->json($posts);
    }

    public function show(Post $post): JsonResponse
    {
        abort_unless(
            $post->status === Post::STATUS_PUBLISHED && $post->published_at?->lessThanOrEqualTo(now()),
            404,
        );

        return response()->json(['data' => $this->present($post)]);
    }

    private function present(Post $post): array
    {
        return [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'excerpt' => $post->excerpt,
            'body' => $post->body,
            'image_url' => $post->imageUrl(),
            'youtube_url' => $post->youtube_url,
            'event_date' => $post->event_date?->toDateString(),
            'location' => $post->location,
            'published_at' => $post->published_at?->toIso8601String(),
        ];
    }
}
