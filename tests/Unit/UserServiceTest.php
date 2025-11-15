<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserService $userService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userService = app(UserService::class);
    }

    public function test_register_creates_new_user_with_normalized_email(): void
    {
        Mail::fake();

        $result = $this->userService->register('Test@Example.COM', 'password123');

        $this->assertTrue($result);
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'auth_type' => 'password',
        ]);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_register_sends_confirmation_email(): void
    {
        Mail::fake();

        $this->userService->register('test@example.com', 'password123');

        Mail::assertSent(\App\Mail\ConfirmEmail::class);
    }

    public function test_register_prevents_duplicate_email(): void
    {
        Mail::fake();

        $this->userService->register('test@example.com', 'password123');
        $result = $this->userService->register('test@example.com', 'password456');

        $this->assertFalse($result);
        $this->assertEquals(1, User::where('email', 'test@example.com')->count());
    }

    public function test_register_normalizes_email_to_lowercase(): void
    {
        Mail::fake();

        $this->userService->register('TEST@EXAMPLE.COM', 'password123');
        $result = $this->userService->register('test@example.com', 'password456');

        $this->assertFalse($result);
    }

    public function test_login_succeeds_with_correct_credentials(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'auth_type' => 'password',
        ]);

        $result = $this->userService->login('test@example.com', 'password123');

        $this->assertTrue($result);
        $this->assertAuthenticatedAs(User::where('email', 'test@example.com')->first());
    }

    public function test_login_fails_with_incorrect_password(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'auth_type' => 'password',
        ]);

        $result = $this->userService->login('test@example.com', 'wrongpassword');

        $this->assertFalse($result);
        $this->assertGuest();
    }

    public function test_login_fails_with_nonexistent_email(): void
    {
        $result = $this->userService->login('nonexistent@example.com', 'password123');

        $this->assertFalse($result);
        $this->assertGuest();
    }

    public function test_confirm_email_marks_user_as_verified(): void
    {
        $user = User::factory()->unverified()->create();

        $this->assertNull($user->email_verified_at);

        $this->userService->confirmEmail($user->id);

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_confirm_email_handles_invalid_user_id(): void
    {
        $this->userService->confirmEmail('invalid-uuid');

        // Should not throw exception
        $this->assertTrue(true);
    }

    public function test_set_password_updates_password_with_correct_previous_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword'),
        ]);

        $result = $this->userService->setPassword($user->id, 'oldpassword', 'newpassword');

        $this->assertTrue($result);
        $user->refresh();
        $this->assertTrue(Hash::check('newpassword', $user->password));
    }

    public function test_set_password_fails_with_incorrect_previous_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword'),
        ]);

        $result = $this->userService->setPassword($user->id, 'wrongpassword', 'newpassword');

        $this->assertFalse($result);
        $user->refresh();
        $this->assertTrue(Hash::check('oldpassword', $user->password));
    }

    public function test_reset_password_updates_user_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword'),
        ]);

        $this->userService->resetPassword($user->id, 'newpassword');

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword', $user->password));
    }

    public function test_get_user_by_email_returns_user(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $result = $this->userService->getUserByEmail('test@example.com');

        $this->assertNotNull($result);
        $this->assertEquals($user->id, $result->id);
    }

    public function test_get_user_by_email_returns_null_for_nonexistent_email(): void
    {
        $result = $this->userService->getUserByEmail('nonexistent@example.com');

        $this->assertNull($result);
    }

    public function test_get_user_by_id_returns_user(): void
    {
        $user = User::factory()->create();

        $result = $this->userService->getUserById($user->id);

        $this->assertNotNull($result);
        $this->assertEquals($user->id, $result->id);
    }

    public function test_get_user_by_username_returns_user(): void
    {
        $user = User::factory()->create(['name' => 'testuser']);

        $result = $this->userService->getUserByUsername('testuser');

        $this->assertNotNull($result);
        $this->assertEquals($user->id, $result->id);
    }

    public function test_on_third_party_callback_creates_new_user(): void
    {
        $result = $this->userService->onThirdPartyCallback('google', 'test@example.com', 'avatar.jpg');

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'auth_type' => 'google',
        ]);
        $this->assertAuthenticated();
    }

    public function test_on_third_party_callback_logs_in_existing_user(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'auth_type' => 'google',
        ]);

        $this->userService->onThirdPartyCallback('google', 'test@example.com', 'avatar.jpg');

        $this->assertAuthenticatedAs($user);
        $this->assertEquals(1, User::where('email', 'test@example.com')->count());
    }

    public function test_on_third_party_callback_prevents_auth_type_mismatch(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'auth_type' => 'password',
        ]);

        $result = $this->userService->onThirdPartyCallback('google', 'test@example.com', 'avatar.jpg');

        $this->assertFalse($result);
        $this->assertGuest();
    }
}
