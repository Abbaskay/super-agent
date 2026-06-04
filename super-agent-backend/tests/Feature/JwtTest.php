<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JwtTest extends TestCase
{
    use RefreshDatabase;

    protected JwtService $jwt;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jwt = app(JwtService::class);
    }

    public function test_jwt_token_issuance(): void
    {
        $user = User::factory()->create();
        $token = $this->jwt->issueToken([
            'sub' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
        ]);

        $this->assertIsString($token);
        $this->assertNotEmpty($token);
        $this->assertStringContainsString('.', $token); // JWT has 3 parts

        $payload = $this->jwt->validateToken($token);
        $this->assertNotNull($payload);
        $this->assertEquals($user->id, $payload->sub);
        $this->assertEquals($user->email, $payload->email);
    }

    public function test_jwt_token_expiry(): void
    {
        config(['jwt.ttl' => 0]); // 0 minutes = expired immediately
        $jwt = app(JwtService::class);

        $token = $jwt->issueToken(['sub' => 1, 'email' => 'test@test.com', 'name' => 'Test']);
        $payload = $jwt->validateToken($token);

        $this->assertNull($payload);
    }

    public function test_validate_token_endpoint(): void
    {
        $user = User::factory()->create();
        $token = $this->jwt->issueToken(['sub' => $user->id, 'email' => $user->email, 'name' => $user->name]);

        $response = $this->postJson('/api/validate-token', ['token' => $token]);

        $response->assertStatus(200)
            ->assertJson(['valid' => true]);
    }

    public function test_validate_token_rejects_invalid(): void
    {
        $response = $this->postJson('/api/validate-token', ['token' => 'invalid.jwt.token']);

        $response->assertStatus(401)
            ->assertJson(['valid' => false]);
    }

    public function test_sso_endpoint_requires_auth(): void
    {
        $response = $this->get('/sso/docs');

        $this->assertContains($response->getStatusCode(), [401, 302]);
        // Either 401 (no token) or 302 (redirect to login)
    }

    public function test_sso_endpoint_with_valid_token(): void
    {
        $user = User::factory()->create();
        $token = $this->jwt->issueToken(['sub' => $user->id, 'email' => $user->email, 'name' => $user->name]);

        $response = $this->withToken($token)->get('/sso/docs');

        $response->assertStatus(302);
        $location = $response->headers->get('Location');
        $this->assertStringContainsString('token=', $location);
        $this->assertStringContainsString('localhost:8002', $location);
    }

    public function test_jwt_token_rejects_tampered(): void
    {
        $user = User::factory()->create();
        $token = $this->jwt->issueToken(['sub' => $user->id, 'email' => $user->email, 'name' => $user->name]);

        // Tamper with the payload (middle part of JWT)
        $parts = explode('.', $token);
        $tamperedPayload = base64_encode('{"sub":999,"email":"hacker@evil.com","name":"Hacker"}');
        $tamperedToken = $parts[0] . '.' . $tamperedPayload . '.' . $parts[2];

        $payload = $this->jwt->validateToken($tamperedToken);
        $this->assertNull($payload);
    }
}
