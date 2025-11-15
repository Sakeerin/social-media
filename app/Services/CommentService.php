<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Http\Responses\CommentResponse;
use App\Models\Comment;


class CommentService
{

    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function createComment(string $userId, string $postId, string $content)
    {
        return DB::transaction(function () use ($userId, $postId, $content) {
            Comment::create([
                "user_id" => $userId,
                "post_id" => $postId,
                "content" => $content,
            ]);

            try {
                Redis::incr($this->getCommentCountCacheKey($postId));
            } catch (\Exception $e) {
                // Redis not available, skip caching
                \Log::warning('Redis not available for comment count caching: ' . $e->getMessage());
            }

            return $this->getPostComments($postId);
        });
    }

    public function getPostComments(string $postId)
    {
        $comments = Comment::where("post_id", $postId)->orderBy("created_at", "desc")->get();

        $res = [];
        foreach ($comments as $comment) {
            array_push($res, new CommentResponse(
                comment: $comment,
                author: $this->userService->getUserMetaById($comment->user_id)
            ));
        }

        return $res;
    }

    public function getCommentCount(string $postId)
    {
        $cacheKey = $this->getCommentCountCacheKey($postId);
        
        try {
            $cached = Redis::get($cacheKey);
            if ($cached != null) {
                return $cached;
            }
        } catch (\Exception $e) {
            // Redis not available, skip caching
            \Log::warning('Redis not available for comment count retrieval: ' . $e->getMessage());
        }

        $count = Comment::where("post_id", $postId)->count();
        
        try {
            Redis::set($cacheKey, $count);
        } catch (\Exception $e) {
            // Redis not available, skip caching
        }

        return $count;
    }

    public function updateComment(string $userId, string $commentId, string $content): Comment | null
    {
        $comment = Comment::query()
            ->where("user_id", $userId)
            ->where("id", $commentId)
            ->first();

        if ($comment == null) {
            return null;
        }

        $comment->content = $content;
        $comment->save();
        return $comment;
    }

    public function deleteComment(string $userId, string $commentId): bool
    {
        $comment = Comment::query()
            ->where("user_id", $userId)
            ->where("id", $commentId)
            ->first();

        if ($comment == null) {
            return false;
        }

        $postId = $comment->post_id; // Store before delete
        $comment->delete();

        // Decrement cache count
        try {
            Redis::decr($this->getCommentCountCacheKey($postId));
        } catch (\Exception $e) {
            // Redis not available, skip caching
            \Log::warning('Redis not available for comment count decrement: ' . $e->getMessage());
        }

        return true;
    }

    private function getCommentCountCacheKey(string $postId): string
    {
        return "comment_count:$postId";
    }
}