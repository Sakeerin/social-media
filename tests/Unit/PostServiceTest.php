<?php

namespace Tests\Unit;

use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use App\Services\PostService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostServiceTest extends TestCase
{
    use RefreshDatabase;

    private PostService $postService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->postService = app(PostService::class);
    }

    public function test_get_posts_returns_paginated_posts_ordered_by_date(): void
    {
        $user = User::factory()->create();

        // Create posts with different timestamps
        $oldPost = Post::factory()->create(['created_at' => now()->subDays(2)]);
        $newPost = Post::factory()->create(['created_at' => now()->subDay()]);
        $newestPost = Post::factory()->create(['created_at' => now()]);

        $result = $this->postService->getPosts($user->id, null);

        $this->assertCount(3, $result->data);
        $this->assertEquals($newestPost->id, $result->data[0]->post->id);
        $this->assertEquals($newPost->id, $result->data[1]->post->id);
        $this->assertEquals($oldPost->id, $result->data[2]->post->id);
    }

    public function test_get_posts_filters_by_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $user1Post = Post::factory()->create(['user_id' => $user1->id]);
        $user2Post = Post::factory()->create(['user_id' => $user2->id]);

        $result = $this->postService->getPosts(null, $user1->id);

        $this->assertCount(1, $result->data);
        $this->assertEquals($user1Post->id, $result->data[0]->post->id);
    }

    public function test_get_posts_includes_like_count(): void
    {
        $post = Post::factory()->create();
        Like::factory()->count(3)->create(['post_id' => $post->id]);

        $result = $this->postService->getPosts(null, null);

        $this->assertEquals(3, $result->data[0]->likes);
    }

    public function test_get_posts_includes_comment_count(): void
    {
        $post = Post::factory()->create();
        Comment::factory()->count(5)->create(['post_id' => $post->id]);

        $result = $this->postService->getPosts(null, null);

        $this->assertEquals(5, $result->data[0]->comments);
    }

    public function test_get_posts_includes_user_liked_status(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $like = Like::factory()->create(['user_id' => $user->id, 'post_id' => $post->id]);

        $result = $this->postService->getPosts($user->id, null);

        $this->assertEquals($like->id, $result->data[0]->liked);
    }

    public function test_get_posts_includes_author_metadata(): void
    {
        $author = User::factory()->create(['name' => 'testauthor']);
        $post = Post::factory()->create(['user_id' => $author->id]);

        $result = $this->postService->getPosts(null, null);

        $this->assertNotNull($result->data[0]->author);
        $this->assertEquals($author->id, $result->data[0]->author->id);
        $this->assertEquals('testauthor', $result->data[0]->author->username);
    }

    public function test_get_posts_paginates_with_5_per_page(): void
    {
        Post::factory()->count(7)->create();

        $result = $this->postService->getPosts(null, null);

        $this->assertCount(5, $result->data);
        $this->assertEquals(1, $result->current_page);
        $this->assertEquals(2, $result->last_page);
    }

    public function test_create_post_creates_post_with_caption(): void
    {
        $user = User::factory()->create();

        $result = $this->postService->createPost($user->id, 'Test caption', null);

        $this->assertDatabaseHas('posts', [
            'user_id' => $user->id,
            'caption' => 'Test caption',
            'image' => null,
        ]);
        $this->assertEquals('Test caption', $result->post->caption);
    }

    public function test_create_post_creates_post_with_image(): void
    {
        $user = User::factory()->create();

        $result = $this->postService->createPost($user->id, 'Test caption', 'image.jpg');

        $this->assertDatabaseHas('posts', [
            'user_id' => $user->id,
            'caption' => 'Test caption',
            'image' => 'image.jpg',
        ]);
    }

    public function test_create_post_includes_author_metadata(): void
    {
        $user = User::factory()->create(['name' => 'testuser']);

        $result = $this->postService->createPost($user->id, 'Test caption', null);

        $this->assertNotNull($result->author);
        $this->assertEquals($user->id, $result->author->id);
    }

    public function test_create_post_initializes_counts_to_zero(): void
    {
        $user = User::factory()->create();

        $result = $this->postService->createPost($user->id, 'Test caption', null);

        $this->assertEquals(0, $result->likes);
        $this->assertEquals(0, $result->comments);
        $this->assertNull($result->liked);
    }

    public function test_update_post_updates_caption(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'caption' => 'Original caption',
        ]);

        $result = $this->postService->updatePost($user->id, $post->id, 'Updated caption');

        $this->assertNotNull($result);
        $this->assertEquals('Updated caption', $result->caption);
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'caption' => 'Updated caption',
        ]);
    }

    public function test_update_post_returns_null_for_nonexistent_post(): void
    {
        $user = User::factory()->create();

        $result = $this->postService->updatePost($user->id, 'nonexistent-id', 'New caption');

        $this->assertNull($result);
    }

    public function test_update_post_prevents_updating_other_users_posts(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $user1->id,
            'caption' => 'Original caption',
        ]);

        $result = $this->postService->updatePost($user2->id, $post->id, 'Hacked caption');

        $this->assertNull($result);
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'caption' => 'Original caption',
        ]);
    }

    public function test_delete_post_deletes_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $result = $this->postService->deletePost($user->id, $post->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_delete_post_prevents_deleting_other_users_posts(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user1->id]);

        $result = $this->postService->deletePost($user2->id, $post->id);

        $this->assertFalse($result);
        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }

    public function test_delete_post_returns_false_for_nonexistent_post(): void
    {
        $user = User::factory()->create();

        $result = $this->postService->deletePost($user->id, 'nonexistent-id');

        $this->assertFalse($result);
    }
}
