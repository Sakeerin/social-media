<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePostRequest;
use App\Http\Requests\UpdatePostRequest;
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
        $post = Post::find($id);

        if ($post == null) {
            return response()->json([
                "error" => "Post not found"
            ], status: 404);
        }

        return response()->json($post);
    }

    public function createPost(CreatePostRequest $req)
    {
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
        $validated = $req->validated();
        $post = $this->postService->updatePost(
            userId: Auth::id(),
            id: $id,
            caption: $validated["caption"]
        );

        if ($post == null) {
            return response()->json([
                "error" => "Post not found or you don't have permission to update it"
            ], status: 404);
        }

        return response()->json($post);
    }

    public function deletePost(string $id)
    {
        if ($this->postService->deletePost(
            userId: Auth::id(),
            postId: $id
        )) {
            return response()->noContent(status: 200);
        } else {
            return response()->json([
                "error" => "Post not found or you don't have permission to delete it"
            ], status: 404);
        }
    }
}
