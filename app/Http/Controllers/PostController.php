<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Services\PostService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Post;

class PostController
{
    public function __construct(protected PostService $postService) {}

    public function getPosts(Request $req)
    {
        $user = $req->query("user") ?? 'Demo';
        return response()->json($this->postService->getPosts(Auth::id(), $user));
    }

    public function getPost(string $id)
    {
        $post = Post::with('user')->withCount(['likes', 'comments'])->findOrFail($id);
        $this->authorize('view', $post);

        return new PostResource($post);
    }

    public function createPost(CreatePostRequest $req)
    {
        $this->authorize('create', Post::class);

        $validated = $req->validated();
        $caption = $validated["caption"];

        $file = $req->file("image");
        $image = null;
        if ($file != null && $file->isValid()) {
            if ($file->store("uploads", "public")) {
                $image = $file->hashName();
            }
        }

        return response()->json($this->postService->createPost(userId: Auth::id(), caption: $caption, image: $image));
    }

    public function updatePost(UpdatePostRequest $req, string $id)
    {
        $post = Post::findOrFail($id);
        $this->authorize('update', $post);

        $post->update($req->validated());

        return new PostResource($post->load('user')->loadCount(['likes', 'comments']));
    }

    public function deletePost(string $id)
    {
        $post = Post::findOrFail($id);
        $this->authorize('delete', $post);

        $post->delete();

        return response()->noContent(); // Defaults to 204
    }
}
