<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateLikeRequest;
use App\Http\Resources\LikeResource;
use App\Services\LikeService;
use App\Models\Like;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LikeController
{

    public function __construct(protected LikeService $likeService) {}

    public function createLike(CreateLikeRequest $req)
    {
        $this->authorize('create', Like::class);

        $validated = $req->validated();
        $like = $this->likeService->createLike(
            userId: Auth::id(),
            postId: $validated["post_id"]
        );

        if ($like == null) {
            return response()->json([
                "error" => "Like already exists"
            ], status: 400);
        }

        return new LikeResource($like);
    }

    public function toggleLike(string $postId)
    {
        $userId = Auth::id();
        $likeId = $this->likeService->userLikesPost($userId, $postId);

        if ($likeId != null) {
            // User already likes this post, so unlike it
            if ($this->likeService->deleteLike(userId: $userId, likeId: $likeId)) {
                return response()->json([
                    "liked" => false,
                    "message" => "Post unliked successfully"
                ]);
            } else {
                return response()->json([
                    "error" => "Failed to unlike post"
                ], status: 500);
            }
        } else {
            // User doesn't like this post, so like it
            $like = $this->likeService->createLike(
                userId: $userId,
                postId: $postId
            );

            if ($like != null) {
                return response()->json([
                    "liked" => true,
                    "like_id" => $like->id,
                    "message" => "Post liked successfully"
                ]);
            } else {
                return response()->json([
                    "error" => "Failed to like post"
                ], status: 500);
            }
        }
    }

    public function deleteLike(string $id)
    {
        $like = Like::findOrFail($id);
        $this->authorize('delete', $like);

        $like->delete();

        return response()->noContent(); // Defaults to 204
    }
}