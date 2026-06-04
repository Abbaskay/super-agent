<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;

class JwtService
{
    protected string $secret;
    protected string $algo;
    protected int $ttl;

    public function __construct()
    {
        $this->secret = config('jwt.secret');
        $this->algo = config('jwt.algorithm');
        $this->ttl = config('jwt.ttl');
    }

    public function issueToken(array $payload): string
    {
        Log::info('JWT: issuing token', [
            'sub' => $payload['sub'] ?? 'unknown',
            'email' => $payload['email'] ?? 'unknown',
        ]);

        $now = now()->timestamp;
        $payload = array_merge([
            'iat' => $now,
            'exp' => $now + ($this->ttl * 60),
            'iss' => config('app.url'),
        ], $payload);

        $token = JWT::encode($payload, $this->secret, $this->algo);

        Log::info('JWT: token issued', ['ttl_minutes' => $this->ttl]);

        return $token;
    }

    public function validateToken(string $token): ?object
    {
        Log::info('JWT: validating token', ['token_prefix' => mb_substr($token, 0, 10) . '...']);

        try {
            $decoded = JWT::decode($token, new Key($this->secret, $this->algo));
            Log::info('JWT: token valid', ['sub' => $decoded->sub ?? 'unknown']);
            return $decoded;
        } catch (\Exception $e) {
            Log::warning('JWT: token validation failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function issueCookie(array $payload): \Symfony\Component\HttpFoundation\Cookie
    {
        Log::info('JWT: issuing cookie', ['sub' => $payload['sub'] ?? 'unknown']);

        $token = $this->issueToken($payload);
        return Cookie::make(
            config('jwt.cookie'),
            $token,
            $this->ttl,
            '/',
            config('jwt.domain'),
            (bool) env('JWT_SECURE_COOKIE', false),
            true,  // httpOnly
            false, // raw
            'lax'
        );
    }

    public function extractFromCookie(): ?string
    {
        $cookieName = config('jwt.cookie');
        $token = request()->cookie($cookieName);

        Log::info('JWT: extracting from cookie', [
            'cookie_name' => $cookieName,
            'found' => !is_null($token),
        ]);

        return $token;
    }

    public function validateFromCookie(): ?object
    {
        Log::info('JWT: validateFromCookie');

        $token = $this->extractFromCookie();
        if (!$token) {
            Log::warning('JWT: no cookie token to validate');
            return null;
        }

        return $this->validateToken($token);
    }
}
