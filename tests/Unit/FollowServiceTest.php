<?php

namespace Tests\Unit;

use App\Models\Follow;
use App\Models\User;
use App\Services\FollowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FollowServiceTest extends TestCase
{
    use RefreshDatabase;

    private FollowService $followService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->followService = app(FollowService::class);
    }

    public function test_create_follow_creates_follow_relationship(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $result = $this->followService->createFollow($user1->id, $user2->id);

        $this->assertNotNull($result);
        $this->assertDatabaseHas('follows', [
            'from_id' => $user1->id,
            'to_id' => $user2->id,
        ]);
    }

    public function test_create_follow_prevents_duplicate_follows(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $this->followService->createFollow($user1->id, $user2->id);
        $result = $this->followService->createFollow($user1->id, $user2->id);

        $this->assertNull($result);
        $this->assertEquals(1, Follow::where('from_id', $user1->id)->where('to_id', $user2->id)->count());
    }

    public function test_get_follows_from_returns_user_follows(): void
    {
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        Follow::factory()->create(['from_id' => $user->id, 'to_id' => $user2->id]);
        Follow::factory()->create(['from_id' => $user->id, 'to_id' => $user3->id]);

        $result = $this->followService->getFollowsFrom($user->id);

        $this->assertCount(2, $result);
    }

    public function test_get_follows_from_returns_empty_for_user_with_no_follows(): void
    {
        $user = User::factory()->create();

        $result = $this->followService->getFollowsFrom($user->id);

        $this->assertCount(0, $result);
    }

    public function test_user_follows_user_returns_follow_id_when_following(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $follow = Follow::factory()->create([
            'from_id' => $user1->id,
            'to_id' => $user2->id,
        ]);

        $result = $this->followService->userFollowsUser($user1->id, $user2->id);

        $this->assertEquals($follow->id, $result);
    }

    public function test_user_follows_user_returns_null_when_not_following(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $result = $this->followService->userFollowsUser($user1->id, $user2->id);

        $this->assertEquals('null', $result);
    }

    public function test_delete_follow_deletes_follow_relationship(): void
    {
        $user = User::factory()->create();
        $follow = Follow::factory()->create(['from_id' => $user->id]);

        $result = $this->followService->deleteFollow($user->id, $follow->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('follows', ['id' => $follow->id]);
    }

    public function test_delete_follow_prevents_deleting_other_users_follows(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $follow = Follow::factory()->create(['from_id' => $user1->id]);

        $result = $this->followService->deleteFollow($user2->id, $follow->id);

        $this->assertFalse($result);
        $this->assertDatabaseHas('follows', ['id' => $follow->id]);
    }

    public function test_delete_follow_returns_false_for_nonexistent_follow(): void
    {
        $user = User::factory()->create();

        $result = $this->followService->deleteFollow($user->id, 'nonexistent-id');

        $this->assertFalse($result);
    }

    public function test_user_can_follow_multiple_users(): void
    {
        $user = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();
        $user4 = User::factory()->create();

        $this->followService->createFollow($user->id, $user2->id);
        $this->followService->createFollow($user->id, $user3->id);
        $this->followService->createFollow($user->id, $user4->id);

        $follows = $this->followService->getFollowsFrom($user->id);

        $this->assertCount(3, $follows);
    }

    public function test_user_can_be_followed_by_multiple_users(): void
    {
        $user = User::factory()->create();
        $follower1 = User::factory()->create();
        $follower2 = User::factory()->create();
        $follower3 = User::factory()->create();

        Follow::factory()->create(['from_id' => $follower1->id, 'to_id' => $user->id]);
        Follow::factory()->create(['from_id' => $follower2->id, 'to_id' => $user->id]);
        Follow::factory()->create(['from_id' => $follower3->id, 'to_id' => $user->id]);

        $this->assertEquals(3, Follow::where('to_id', $user->id)->count());
    }

    public function test_follow_relationship_is_directional(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $this->followService->createFollow($user1->id, $user2->id);

        // User1 follows User2
        $this->assertNotNull($this->followService->userFollowsUser($user1->id, $user2->id));

        // But User2 doesn't follow User1
        $this->assertEquals('null', $this->followService->userFollowsUser($user2->id, $user1->id));
    }
}
