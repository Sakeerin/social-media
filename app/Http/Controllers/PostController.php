<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePostRequest;
use App\Services\PostService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{

    public function getPosts(Request $req)
    {
        $user = $req->query("user");
        return response()->json($this->postService->getPosts(Auth::id(), $user));
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
    public function __construct(protected PostService $postService) {}
}
