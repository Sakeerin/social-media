<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_comment(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $response = $this->actingAs($user)
            ->postJson("/api/posts/{$post->id}/comments", [
                'post_id' => $post->id,
                'content' => 'Great post!',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'post_id' => $post->id,
            'content' => 'Great post!',
        ]);
    }

    public function test_create_comment_requires_authentication(): void
    {
        $post = Post::factory()->create();

        $response = $this->postJson("/api/posts/{$post->id}/comments", [
            'post_id' => $post->id,
            'content' => 'Test comment',
        ]);

        $response->assertStatus(401);
    }

    public function test_create_comment_requires_post_id(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $response = $this->actingAs($user)
            ->postJson("/api/posts/{$post->id}/comments", [
                'content' => 'Test comment',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['post_id']);
    }

    public function test_create_comment_requires_content(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $response = $this->actingAs($user)
            ->postJson("/api/posts/{$post->id}/comments", [
                'post_id' => $post->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }

    public function test_create_comment_content_has_max_length(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $response = $this->actingAs($user)
            ->postJson("/api/posts/{$post->id}/comments", [
                'post_id' => $post->id,
                'content' => str_repeat('a', 101),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }

    public function test_create_comment_validates_post_id_is_uuid(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $response = $this->actingAs($user)
            ->postJson("/api/posts/{$post->id}/comments", [
                'post_id' => 'not-a-uuid',
                'content' => 'Test comment',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['post_id']);
    }

    public function test_get_post_comments_returns_comments(): void
    {
        $post = Post::factory()->create();
        Comment::factory()->count(3)->create(['post_id' => $post->id]);

        $response = $this->getJson("/api/posts/{$post->id}/comments");

        $response->assertStatus(200)
            ->assertJsonCount(3)
            ->assertJsonStructure([
                '*' => [
                    'comment' => ['id', 'content', 'user_id', 'post_id', 'created_at'],
                    'author',
                ],
            ]);
    }

    public function test_get_post_comments_returns_empty_array_for_no_comments(): void
    {
        $post = Post::factory()->create();

        $response = $this->getJson("/api/posts/{$post->id}/comments");

        $response->assertStatus(200)
            ->assertJsonCount(0);
    }

    public function test_authenticated_user_can_update_own_comment(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->id,
            'content' => 'Original comment',
        ]);

        $response = $this->actingAs($user)
            ->putJson("/api/comments/{$comment->id}", [
                'content' => 'Updated comment',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Updated comment',
        ]);
    }

    public function test_user_cannot_update_other_users_comment(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user1->id,
            'content' => 'Original comment',
        ]);

        $response = $this->actingAs($user2)
            ->putJson("/api/comments/{$comment->id}", [
                'content' => 'Hacked comment',
            ]);

        $response->assertStatus(404);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Original comment',
        ]);
    }

    public function test_update_comment_requires_authentication(): void
    {
        $comment = Comment::factory()->create();

        $response = $this->putJson("/api/comments/{$comment->id}", [
            'content' => 'Updated',
        ]);

        $response->assertStatus(401);
    }

    public function test_update_comment_requires_content(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->putJson("/api/comments/{$comment->id}", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }

    public function test_authenticated_user_can_delete_own_comment(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_user_cannot_delete_other_users_comment(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user1->id]);

        $response = $this->actingAs($user2)
            ->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(404);
        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    }

    public function test_delete_comment_requires_authentication(): void
    {
        $comment = Comment::factory()->create();

        $response = $this->deleteJson("/api/comments/{$comment->id}");

        $response->assertStatus(401);
    }
}
