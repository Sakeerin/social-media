<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Services\CommentService;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;

class CommentController
{

    public function __construct(protected CommentService $commentService) {}

    public function createComment(CreateCommentRequest $req)
    {
        $this->authorize('create', Comment::class);

        $validated = $req->validated();
        return $this->commentService->createComment(
            userId: Auth::id(),
            postId: $validated["post_id"],
            content: $validated["content"]
        );
    }

    public function getPostComments(string $postId)
    {
        return $this->commentService->getPostComments($postId);
    }

    public function updateComment(UpdateCommentRequest $req, string $id)
    {
        $comment = Comment::findOrFail($id);
        $this->authorize('update', $comment);

        $comment->update($req->validated());

        return new CommentResource($comment->load('user'));
    }

    public function deleteComment(string $id)
    {
        $comment = Comment::findOrFail($id);
        $this->authorize('delete', $comment);

        $comment->delete();

        return response()->noContent(); // Defaults to 204
    }
}