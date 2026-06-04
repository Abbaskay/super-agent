<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected JwtService $jwt;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jwt = app(JwtService::class);
    }

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email']]);
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    public function test_register_rejects_short_password(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => 'Ab1',
        ]);

        $response->assertStatus(422);
    }

    public function test_register_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'dup@example.com']);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Dup',
            'email' => 'dup@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
    }

    public function test_user_can_login(): void
    {
        User::factory()->create([
            'email' => 'login@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'user']);
    }

    public function test_login_rejects_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'wrong@example.com',
            'password' => bcrypt('correct'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrong',
        ]);

        $response->assertStatus(422);
    }

    public function test_authenticated_user_can_access_me(): void
    {
        $user = User::factory()->create();
        $token = $this->jwt->issueToken(['sub' => $user->id, 'email' => $user->email, 'name' => $user->name]);

        $response = $this->withToken($token)->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJsonStructure(['user' => ['id', 'name', 'email']]);
    }

    public function test_unauthenticated_user_cannot_access_me(): void
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $this->jwt->issueToken(['sub' => $user->id, 'email' => $user->email, 'name' => $user->name]);

        $response = $this->withToken($token)->postJson('/api/auth/logout');

        $response->assertStatus(200);
    }

    public function test_rate_limiting_on_login(): void
    {
        $attempts = 11;
        $lastStatus = null;

        for ($i = 0; $i < $attempts; $i++) {
            $response = $this->postJson('/api/auth/login', [
                'email' => 'ratelimit@example.com',
                'password' => 'wrong',
            ]);
            $lastStatus = $response->getStatusCode();
        }

        $this->assertEquals(429, $lastStatus);
    }

    public function test_rate_limiting_on_register(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $response = $this->postJson('/api/auth/register', [
                'name' => "User $i",
                'email' => "rate$i@example.com",
                'password' => 'password123',
            ]);
        }

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Blocked',
            'email' => 'blocked@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(429);
    }
}
