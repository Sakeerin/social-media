<?php

namespace Tests\Feature;

use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LikeTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_like_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/likes', [
                'post_id' => $post->id,
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);
    }

    public function test_like_post_requires_authentication(): void
    {
        $post = Post::factory()->create();

        $response = $this->postJson('/api/likes', [
            'post_id' => $post->id,
        ]);

        $response->assertStatus(401);
    }

    public function test_like_post_requires_post_id(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/likes', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['post_id']);
    }

    public function test_like_post_validates_post_id_is_uuid(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/likes', [
                'post_id' => 'not-a-uuid',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['post_id']);
    }

    public function test_user_cannot_like_same_post_twice(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        // First like should succeed
        $this->actingAs($user)
            ->postJson('/api/likes', ['post_id' => $post->id])
            ->assertStatus(200);

        // Second like should fail
        $response = $this->actingAs($user)
            ->postJson('/api/likes', ['post_id' => $post->id]);

        $response->assertStatus(404);
        $this->assertEquals(1, Like::where('user_id', $user->id)->where('post_id', $post->id)->count());
    }

    public function test_authenticated_user_can_unlike_post(): void
    {
        $user = User::factory()->create();
        $like = Like::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->deleteJson("/api/likes/{$like->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('likes', ['id' => $like->id]);
    }

    public function test_unlike_post_requires_authentication(): void
    {
        $like = Like::factory()->create();

        $response = $this->deleteJson("/api/likes/{$like->id}");

        $response->assertStatus(401);
    }

    public function test_user_cannot_unlike_other_users_like(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $like = Like::factory()->create(['user_id' => $user1->id]);

        $response = $this->actingAs($user2)
            ->deleteJson("/api/likes/{$like->id}");

        $response->assertStatus(404);
        $this->assertDatabaseHas('likes', ['id' => $like->id]);
    }

    public function test_toggle_like_on_post_creates_like_when_not_liked(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $response = $this->actingAs($user)
            ->postJson("/api/posts/{$post->id}/like");

        $response->assertStatus(200);
        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);
    }

    public function test_toggle_like_on_post_removes_like_when_already_liked(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $like = Like::factory()->create([
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);

        $response = $this->actingAs($user)
            ->postJson("/api/posts/{$post->id}/like");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('likes', ['id' => $like->id]);
    }

    public function test_toggle_like_requires_authentication(): void
    {
        $post = Post::factory()->create();

        $response = $this->postJson("/api/posts/{$post->id}/like");

        $response->assertStatus(401);
    }

    public function test_multiple_users_can_like_same_post(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($user1)->postJson('/api/likes', ['post_id' => $post->id]);
        $this->actingAs($user2)->postJson('/api/likes', ['post_id' => $post->id]);
        $this->actingAs($user3)->postJson('/api/likes', ['post_id' => $post->id]);

        $this->assertEquals(3, Like::where('post_id', $post->id)->count());
    }

    public function test_user_can_like_multiple_posts(): void
    {
        $user = User::factory()->create();
        $post1 = Post::factory()->create();
        $post2 = Post::factory()->create();
        $post3 = Post::factory()->create();

        $this->actingAs($user)->postJson('/api/likes', ['post_id' => $post1->id]);
        $this->actingAs($user)->postJson('/api/likes', ['post_id' => $post2->id]);
        $this->actingAs($user)->postJson('/api/likes', ['post_id' => $post3->id]);

        $this->assertEquals(3, Like::where('user_id', $user->id)->count());
    }
}
