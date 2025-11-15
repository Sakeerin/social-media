<?php

namespace Tests\Unit;

use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use App\Services\LikeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LikeServiceTest extends TestCase
{
    use RefreshDatabase;

    private LikeService $likeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->likeService = app(LikeService::class);
    }

    public function test_create_like_creates_like(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $result = $this->likeService->createLike($user->id, $post->id);

        $this->assertNotNull($result);
        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);
    }

    public function test_create_like_prevents_duplicate_likes(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->likeService->createLike($user->id, $post->id);
        $result = $this->likeService->createLike($user->id, $post->id);

        $this->assertNull($result);
        $this->assertEquals(1, Like::where('user_id', $user->id)->where('post_id', $post->id)->count());
    }

    public function test_get_like_count_returns_correct_count(): void
    {
        $post = Post::factory()->create();
        Like::factory()->count(7)->create(['post_id' => $post->id]);

        $result = $this->likeService->getLikeCount($post->id);

        $this->assertEquals(7, $result);
    }

    public function test_get_like_count_returns_zero_for_post_without_likes(): void
    {
        $post = Post::factory()->create();

        $result = $this->likeService->getLikeCount($post->id);

        $this->assertEquals(0, $result);
    }

    public function test_user_likes_post_returns_like_id_when_user_liked_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $like = Like::factory()->create([
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);

        $result = $this->likeService->userLikesPost($user->id, $post->id);

        $this->assertEquals($like->id, $result);
    }

    public function test_user_likes_post_returns_null_when_user_has_not_liked_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $result = $this->likeService->userLikesPost($user->id, $post->id);

        $this->assertNull($result);
    }

    public function test_delete_like_deletes_like(): void
    {
        $user = User::factory()->create();
        $like = Like::factory()->create(['user_id' => $user->id]);

        $result = $this->likeService->deleteLike($user->id, $like->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('likes', ['id' => $like->id]);
    }

    public function test_delete_like_prevents_deleting_other_users_likes(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $like = Like::factory()->create(['user_id' => $user1->id]);

        $result = $this->likeService->deleteLike($user2->id, $like->id);

        $this->assertFalse($result);
        $this->assertDatabaseHas('likes', ['id' => $like->id]);
    }

    public function test_delete_like_returns_false_for_nonexistent_like(): void
    {
        $user = User::factory()->create();

        $result = $this->likeService->deleteLike($user->id, 'nonexistent-id');

        $this->assertFalse($result);
    }

    public function test_multiple_users_can_like_same_post(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();
        $post = Post::factory()->create();

        $this->likeService->createLike($user1->id, $post->id);
        $this->likeService->createLike($user2->id, $post->id);
        $this->likeService->createLike($user3->id, $post->id);

        $count = $this->likeService->getLikeCount($post->id);

        $this->assertEquals(3, $count);
    }

    public function test_user_can_like_multiple_posts(): void
    {
        $user = User::factory()->create();
        $post1 = Post::factory()->create();
        $post2 = Post::factory()->create();
        $post3 = Post::factory()->create();

        $this->likeService->createLike($user->id, $post1->id);
        $this->likeService->createLike($user->id, $post2->id);
        $this->likeService->createLike($user->id, $post3->id);

        $this->assertEquals(3, Like::where('user_id', $user->id)->count());
    }
}
