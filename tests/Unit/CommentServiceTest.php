<?php

namespace Tests\Unit;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Services\CommentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class CommentServiceTest extends TestCase
{
    use RefreshDatabase;

    private CommentService $commentService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commentService = app(CommentService::class);
    }

    public function test_create_comment_creates_comment(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->commentService->createComment($user->id, $post->id, 'Test comment');

        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'post_id' => $post->id,
            'content' => 'Test comment',
        ]);
    }

    public function test_create_comment_returns_post_comments(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $result = $this->commentService->createComment($user->id, $post->id, 'Test comment');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
    }

    public function test_get_post_comments_returns_comments_ordered_by_date(): void
    {
        $post = Post::factory()->create();

        $oldComment = Comment::factory()->create([
            'post_id' => $post->id,
            'created_at' => now()->subDays(2),
        ]);
        $newComment = Comment::factory()->create([
            'post_id' => $post->id,
            'created_at' => now()->subDay(),
        ]);
        $newestComment = Comment::factory()->create([
            'post_id' => $post->id,
            'created_at' => now(),
        ]);

        $result = $this->commentService->getPostComments($post->id);

        $this->assertCount(3, $result);
        $this->assertEquals($newestComment->id, $result[0]->comment->id);
        $this->assertEquals($newComment->id, $result[1]->comment->id);
        $this->assertEquals($oldComment->id, $result[2]->comment->id);
    }

    public function test_get_post_comments_includes_author_metadata(): void
    {
        $author = User::factory()->create(['name' => 'testauthor']);
        $post = Post::factory()->create();
        Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => $author->id,
        ]);

        $result = $this->commentService->getPostComments($post->id);

        $this->assertNotNull($result[0]->author);
        $this->assertEquals($author->id, $result[0]->author->id);
        $this->assertEquals('testauthor', $result[0]->author->username);
    }

    public function test_get_post_comments_returns_empty_array_for_post_without_comments(): void
    {
        $post = Post::factory()->create();

        $result = $this->commentService->getPostComments($post->id);

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    public function test_get_comment_count_returns_correct_count(): void
    {
        $post = Post::factory()->create();
        Comment::factory()->count(5)->create(['post_id' => $post->id]);

        $result = $this->commentService->getCommentCount($post->id);

        $this->assertEquals(5, $result);
    }

    public function test_get_comment_count_returns_zero_for_post_without_comments(): void
    {
        $post = Post::factory()->create();

        $result = $this->commentService->getCommentCount($post->id);

        $this->assertEquals(0, $result);
    }

    public function test_update_comment_updates_content(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->id,
            'content' => 'Original content',
        ]);

        $result = $this->commentService->updateComment($user->id, $comment->id, 'Updated content');

        $this->assertNotNull($result);
        $this->assertEquals('Updated content', $result->content);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Updated content',
        ]);
    }

    public function test_update_comment_returns_null_for_nonexistent_comment(): void
    {
        $user = User::factory()->create();

        $result = $this->commentService->updateComment($user->id, 'nonexistent-id', 'New content');

        $this->assertNull($result);
    }

    public function test_update_comment_prevents_updating_other_users_comments(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user1->id,
            'content' => 'Original content',
        ]);

        $result = $this->commentService->updateComment($user2->id, $comment->id, 'Hacked content');

        $this->assertNull($result);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Original content',
        ]);
    }

    public function test_delete_comment_deletes_comment(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);

        $result = $this->commentService->deleteComment($user->id, $comment->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_delete_comment_prevents_deleting_other_users_comments(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user1->id]);

        $result = $this->commentService->deleteComment($user2->id, $comment->id);

        $this->assertFalse($result);
        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    }

    public function test_delete_comment_returns_false_for_nonexistent_comment(): void
    {
        $user = User::factory()->create();

        $result = $this->commentService->deleteComment($user->id, 'nonexistent-id');

        $this->assertFalse($result);
    }
}
