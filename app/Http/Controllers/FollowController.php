<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateFollowRequest;
use App\Http\Resources\FollowResource;
use App\Services\FollowService;
use App\Services\UserService;
use App\Models\Follow;
use Illuminate\Support\Facades\Auth;

class FollowController
{

    public function __construct(protected FollowService $followService, protected UserService $userService) {}

    public function getFollows()
    {
        $follows = $this->followService->getFollowsFrom(userId: Auth::id());
        return FollowResource::collection($follows);
    }

    public function createFollow(CreateFollowRequest $req)
    {
        $this->authorize('create', Follow::class);

        $validated = $req->validated();

        // Prevent self-follow
        if (Auth::id() === $validated["to_id"]) {
            return response()->json([
                "error" => "You cannot follow yourself"
            ], status: 400);
        }

        $follow = $this->followService->createFollow(
            fromId: Auth::id(),
            toId: $validated["to_id"]
        );

        if ($follow == null) {
            return response()->json([
                "error" => "You are already following this user"
            ], status: 400);
        }

        return new FollowResource($follow);
    }

    public function getFollow(string $toUser)
    {
        $followId = $this->followService->userFollowsUser(Auth::id(), $toUser);
        if ($followId == null) {
            return response('', status: 404);
        }

        return response($followId);
    }

    public function deleteFollow(string $id)
    {
        $follow = Follow::findOrFail($id);
        $this->authorize('delete', $follow);

        $follow->delete();

        return response()->noContent(); // Defaults to 204
    }

    public function toggleFollow(string $userId)
    {
        $fromId = Auth::id();

        // Prevent self-follow
        if ($fromId === $userId) {
            return response()->json([
                "error" => "You cannot follow yourself"
            ], status: 400);
        }

        $followId = $this->followService->userFollowsUser($fromId, $userId);

        if ($followId != null && $followId != "null") {
            // User already follows this user, so unfollow
            $follow = Follow::findOrFail($followId);
            $this->authorize('delete', $follow);

            $follow->delete();

            return response()->json([
                "following" => false,
                "message" => "User unfollowed successfully"
            ]);
        } else {
            // User doesn't follow this user, so follow
            $this->authorize('create', Follow::class);

            $follow = $this->followService->createFollow(
                fromId: $fromId,
                toId: $userId
            );

            if ($follow != null) {
                return response()->json([
                    "following" => true,
                    "follow_id" => $follow->id,
                    "message" => "User followed successfully"
                ]);
            } else {
                return response()->json([
                    "error" => "You are already following this user"
                ], status: 400);
            }
        }
    }

    public function getFollowers(string $userId)
    {
        $followers = $this->followService->getFollowersOf($userId);
        return FollowResource::collection($followers);
    }

    public function getFollowing(string $userId)
    {
        $following = $this->followService->getFollowsFrom($userId);
        return FollowResource::collection($following);
    }
}