<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_posts_returns_posts_list(): void
    {
        Post::factory()->count(3)->create();

        $response = $this->getJson('/api/posts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'post' => ['id', 'caption', 'user_id', 'image', 'created_at'],
                        'likes',
                        'comments',
                        'author',
                    ],
                ],
                'current_page',
                'last_page',
            ]);
    }

    public function test_get_posts_filters_by_user(): void
    {
        $user = User::factory()->create();
        Post::factory()->count(2)->create(['user_id' => $user->id]);
        Post::factory()->count(3)->create();

        $response = $this->getJson("/api/posts?user={$user->id}");

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(2, $data);
    }

    public function test_authenticated_user_can_create_post(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/posts', [
                'caption' => 'My test post',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'post' => ['id', 'caption', 'user_id'],
                'likes',
                'comments',
                'author',
            ]);

        $this->assertDatabaseHas('posts', [
            'user_id' => $user->id,
            'caption' => 'My test post',
        ]);
    }

    public function test_create_post_requires_authentication(): void
    {
        $response = $this->postJson('/api/posts', [
            'caption' => 'Test post',
        ]);

        $response->assertStatus(401);
    }

    public function test_create_post_requires_caption(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/posts', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['caption']);
    }

    public function test_create_post_caption_has_max_length(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/posts', [
                'caption' => str_repeat('a', 101),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['caption']);
    }

    public function test_create_post_with_image(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/api/posts', [
                'caption' => 'Post with image',
                'image' => UploadedFile::fake()->image('photo.jpg'),
            ]);

        $response->assertStatus(200);

        $post = Post::where('user_id', $user->id)->first();
        $this->assertNotNull($post->image);
        Storage::disk('public')->assertExists('uploads/' . $post->image);
    }

    public function test_create_post_validates_image_type(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/api/posts', [
                'caption' => 'Post with invalid image',
                'image' => UploadedFile::fake()->create('document.pdf', 100),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    public function test_create_post_validates_image_size(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/api/posts', [
                'caption' => 'Post with large image',
                'image' => UploadedFile::fake()->image('photo.jpg')->size(3000), // 3MB
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }

    public function test_get_single_post_returns_post_details(): void
    {
        $post = Post::factory()->create();

        $response = $this->getJson("/api/posts/{$post->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'caption',
                'user_id',
                'image',
                'created_at',
            ]);
    }

    public function test_authenticated_user_can_update_own_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id, 'caption' => 'Original']);

        $response = $this->actingAs($user)
            ->putJson("/api/posts/{$post->id}", [
                'caption' => 'Updated caption',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'caption' => 'Updated caption',
        ]);
    }

    public function test_user_cannot_update_other_users_post(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user1->id, 'caption' => 'Original']);

        $response = $this->actingAs($user2)
            ->putJson("/api/posts/{$post->id}", [
                'caption' => 'Hacked',
            ]);

        $response->assertStatus(404);
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'caption' => 'Original',
        ]);
    }

    public function test_update_post_requires_authentication(): void
    {
        $post = Post::factory()->create();

        $response = $this->putJson("/api/posts/{$post->id}", [
            'caption' => 'Updated',
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_delete_own_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->deleteJson("/api/posts/{$post->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_user_cannot_delete_other_users_post(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user1->id]);

        $response = $this->actingAs($user2)
            ->deleteJson("/api/posts/{$post->id}");

        $response->assertStatus(404);
        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }

    public function test_delete_post_requires_authentication(): void
    {
        $post = Post::factory()->create();

        $response = $this->deleteJson("/api/posts/{$post->id}");

        $response->assertStatus(401);
    }

    public function test_get_post_comments_returns_comments_list(): void
    {
        $post = Post::factory()->create();
        Comment::factory()->count(3)->create(['post_id' => $post->id]);

        $response = $this->getJson("/api/posts/{$post->id}/comments");

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }
}
