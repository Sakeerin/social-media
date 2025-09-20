<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use App\Models\Like;

class LikeService
{

    public function createLike(string $userId, string $postId): Like | null
    {
        $like = Like::query()
            ->where("user_id", $userId)
            ->where("post_id", $postId)
            ->first();

        if ($like != null) {
            return null;
        }

        $like = Like::create([
            "user_id" => $userId,
            "post_id" => $postId,
        ]);

        try {
            // Increment like count for this post.
            Redis::incr($this->getLikeCountCacheKey($postId));
            // User now likes this post.
            Redis::set($this->getLikeUsersCacheKey($postId, $userId), $like->id);
        } catch (\Exception $e) {
            // Redis not available, skip caching
            \Log::warning('Redis not available for like caching: ' . $e->getMessage());
        }
        return $like;
    }

    public function getLikeCount(string $postId): int
    {
        $cacheKey = $this->getLikeCountCacheKey($postId);
        
        try {
            $cached = Redis::get($cacheKey);
            if ($cached != null) {
                return $cached;
            }
        } catch (\Exception $e) {
            // Redis not available, skip caching
            \Log::warning('Redis not available for like count caching: ' . $e->getMessage());
        }

        // Fetch actual like count, and cache it.
        $likeCount = Like::where("post_id", $postId)->count();
        
        try {
            Redis::set($cacheKey, $likeCount);
        } catch (\Exception $e) {
            // Redis not available, skip caching
        }

        return $likeCount;
    }

    public function userLikesPost(string $userId, string $postId): string | null
    {
        $cacheKey = $this->getLikeUsersCacheKey($postId, $userId);
        
        try {
            $cached = Redis::get($cacheKey);
            if ($cached != null) {
                return $cached == "null" ? null : $cached;
            }
        } catch (\Exception $e) {
            // Redis not available, skip caching
            \Log::warning('Redis not available for user like caching: ' . $e->getMessage());
        }

        // Fetch if user likes post and cache it.
        $like = Like::where("post_id", $postId)
            ->where("user_id", $userId)
            ->first();

        $likeId = $like != null ? $like->id : null;
        
        try {
            Redis::set($cacheKey, $likeId != null ? $likeId : "null");
        } catch (\Exception $e) {
            // Redis not available, skip caching
        }

        return $likeId;
    }

    public function deleteLike(string $userId, string $likeId): bool
    {
        $like = Like::query()
            ->where("user_id", $userId)
            ->where("id", $likeId)
            ->first();

        if ($like == null) {
            return false;
        }

        $like->delete();
        
        try {
            Redis::set($this->getLikeUsersCacheKey($like->post_id, $userId), "null");
            Redis::decr($this->getLikeCountCacheKey($like->post_id));
        } catch (\Exception $e) {
            // Redis not available, skip caching
            \Log::warning('Redis not available for like deletion caching: ' . $e->getMessage());
        }
        
        return true;
    }

    private function getLikeCountCacheKey(string $postId): string
    {
        return "like_count:$postId";
    }

    /**
     * Redis cache key for if a user likes a post.
     */
    private function getLikeUsersCacheKey(string $postId, string $userId): string
    {
        return "like_users:$postId:$userId";
    }
}