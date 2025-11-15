<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateLikeRequest;
use App\Services\LikeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LikeController
{

    public function __construct(protected LikeService $likeService) {}

    public function createLike(CreateLikeRequest $req)
    {
        $validated = $req->validated();
        $like = $this->likeService->createLike(
            userId: Auth::id(),
            postId: $validated["post_id"]
        );

        if ($like == null) {
            return response()->json([
                "error" => "Like already exists"
            ], status: 400);
        } else {
            return $like;
        }
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
        if ($this->likeService->deleteLike(userId: Auth::id(), likeId: $id)) {
            return response()->noContent(status: 200);
        } else {
            return response()->json([
                "error" => "Like not found"
            ], status: 404);
        }
    }
}