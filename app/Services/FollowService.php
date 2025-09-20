<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use App\Models\Follow;


class FollowService
{

    public function getFollowsFrom(string $userId)
    {
        return Follow::query()
            ->where("from_id", $userId)
            ->get();
    }

    public function userFollowsUser(string $fromId, string $toId): string | null
    {
        $cacheKey = $this->followsCacheKey($fromId, $toId);
        
        try {
            $cached = Redis::get($cacheKey);
            if ($cached !== null) {
                return $cached != "null" ? $cached : null;
            }
        } catch (\Exception $e) {
            // Redis not available, skip caching
            \Log::warning('Redis not available for follow caching: ' . $e->getMessage());
        }

        $following = Follow::query()
            ->where("from_id", $fromId)
            ->where("to_id", $toId)
            ->first();

        $value = $following != null ? $following->id : "null";
        
        try {
            Redis::set($cacheKey, $value);
        } catch (\Exception $e) {
            // Redis not available, skip caching
        }
        
        return $value;
    }

    public function createFollow(string $fromId, string $toId): Follow | null
    {
        $existing = Follow::where("from_id", $fromId)
            ->where("to_id", $toId)
            ->first();

        if ($existing != null) {
            return null;
        }

        $follow = Follow::create([
            "from_id" => $fromId,
            "to_id" => $toId,
        ]);

        try {
            Redis::set($this->followsCacheKey($fromId, $toId), $follow->id);
        } catch (\Exception $e) {
            // Redis not available, skip caching
        }
        return $follow;
    }

    public function deleteFollow(string $fromId, string $followId): bool
    {
        $follow = Follow::where("from_id", $fromId)
            ->where("id", $followId)
            ->first();

        if ($follow == null) {
            return false;
        }

        $follow->delete();
        try {
            Redis::set($this->followsCacheKey($fromId, $follow->to_id), "null");
        } catch (\Exception $e) {
            // Redis not available, skip caching
        }
        return true;
    }

    private function followsCacheKey(string $fromId, string $toId): string
    {
        return "follows_{$fromId}_{$toId}";
    }
}