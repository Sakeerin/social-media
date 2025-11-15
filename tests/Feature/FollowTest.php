<?php

namespace Tests\Feature;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FollowTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_follow_another_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $response = $this->actingAs($user1)
            ->postJson('/api/follows', [
                'to_id' => $user2->id,
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('follows', [
            'from_id' => $user1->id,
            'to_id' => $user2->id,
        ]);
    }

    public function test_follow_user_requires_authentication(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/follows', [
            'to_id' => $user->id,
        ]);

        $response->assertStatus(401);
    }

    public function test_follow_user_requires_to_id(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/follows', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['to_id']);
    }

    public function test_follow_user_validates_to_id_is_uuid(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/follows', [
                'to_id' => 'not-a-uuid',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['to_id']);
    }

    public function test_user_cannot_follow_same_user_twice(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // First follow should succeed
        $this->actingAs($user1)
            ->postJson('/api/follows', ['to_id' => $user2->id])
            ->assertStatus(200);

        // Second follow should fail
        $response = $this->actingAs($user1)
            ->postJson('/api/follows', ['to_id' => $user2->id]);

        $response->assertStatus(400)
            ->assertJson(['error' => 'You are already following this user!']);

        $this->assertEquals(1, Follow::where('from_id', $user1->id)->where('to_id', $user2->id)->count());
    }

    public function test_authenticated_user_can_unfollow_user(): void
    {
        $user1 = User::factory()->create();
        $follow = Follow::factory()->create(['from_id' => $user1->id]);

        $response = $this->actingAs($user1)
            ->deleteJson("/api/follows/{$follow->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('follows', ['id' => $follow->id]);
    }

    public function test_unfollow_requires_authentication(): void
    {
        $follow = Follow::factory()->create();

        $response = $this->deleteJson("/api/follows/{$follow->id}");

        $response->assertStatus(401);
    }

    public function test_user_cannot_unfollow_other_users_follows(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $follow = Follow::factory()->create(['from_id' => $user1->id]);

        $response = $this->actingAs($user2)
            ->deleteJson("/api/follows/{$follow->id}");

        $response->assertStatus(404)
            ->assertJson(['error' => 'You are not following this user.']);

        $this->assertDatabaseHas('follows', ['id' => $follow->id]);
    }

    public function test_get_follows_returns_users_following_list(): void
    {
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        Follow::factory()->create(['from_id' => $user->id, 'to_id' => $user2->id]);
        Follow::factory()->create(['from_id' => $user->id, 'to_id' => $user3->id]);

        $response = $this->actingAs($user)
            ->getJson('/api/follows');

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonStructure([
                '*' => [
                    'follow' => ['id', 'from_id', 'to_id'],
                    'user',
                ],
            ]);
    }

    public function test_get_follows_requires_authentication(): void
    {
        $response = $this->getJson('/api/follows');

        $response->assertStatus(401);
    }

    public function test_get_user_followers_returns_followers_list(): void
    {
        $user = User::factory()->create();
        $follower1 = User::factory()->create();
        $follower2 = User::factory()->create();

        Follow::factory()->create(['from_id' => $follower1->id, 'to_id' => $user->id]);
        Follow::factory()->create(['from_id' => $follower2->id, 'to_id' => $user->id]);

        $response = $this->getJson("/api/users/{$user->id}/followers");

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertCount(2, $data);
    }

    public function test_get_user_following_returns_following_list(): void
    {
        $user = User::factory()->create();
        $following1 = User::factory()->create();
        $following2 = User::factory()->create();

        Follow::factory()->create(['from_id' => $user->id, 'to_id' => $following1->id]);
        Follow::factory()->create(['from_id' => $user->id, 'to_id' => $following2->id]);

        $response = $this->getJson("/api/users/{$user->id}/following");

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertCount(2, $data);
    }

    public function test_toggle_follow_creates_follow_when_not_following(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $response = $this->actingAs($user1)
            ->postJson("/api/users/{$user2->id}/follow");

        $response->assertStatus(200);
        $this->assertDatabaseHas('follows', [
            'from_id' => $user1->id,
            'to_id' => $user2->id,
        ]);
    }

    public function test_toggle_follow_removes_follow_when_already_following(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $follow = Follow::factory()->create([
            'from_id' => $user1->id,
            'to_id' => $user2->id,
        ]);

        $response = $this->actingAs($user1)
            ->postJson("/api/users/{$user2->id}/follow");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('follows', ['id' => $follow->id]);
    }

    public function test_toggle_follow_requires_authentication(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson("/api/users/{$user->id}/follow");

        $response->assertStatus(401);
    }

    public function test_follow_relationship_is_directional(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $this->actingAs($user1)
            ->postJson('/api/follows', ['to_id' => $user2->id]);

        // User1 follows User2
        $this->assertDatabaseHas('follows', [
            'from_id' => $user1->id,
            'to_id' => $user2->id,
        ]);

        // But User2 doesn't follow User1
        $this->assertDatabaseMissing('follows', [
            'from_id' => $user2->id,
            'to_id' => $user1->id,
        ]);
    }
}
