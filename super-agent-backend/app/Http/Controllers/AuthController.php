<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use App\Services\JwtService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        protected JwtService $jwt
    ) {}

    public function register(Request $request): JsonResponse
    {
        Log::info('Auth: register attempt', ['email' => $request->input('email')]);

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'tenant_name' => 'nullable|string|max:255',
            ]);
        } catch (ValidationException $e) {
            Log::warning('Auth: register validation failed', ['errors' => $e->errors()]);
            throw $e;
        }

        // Auto-create a tenant for the new user
        $tenantName = $validated['tenant_name'] ?? $validated['name'] . "'s Workspace";
        $tenant = Tenant::create([
            'name' => $tenantName,
            'slug' => Str::slug($tenantName) . '-' . Str::random(6),
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'tenant_id' => $tenant->id,
            'role' => 'owner',
        ]);

        Log::info('Auth: user registered', [
            'user_id' => $user->id,
            'email' => $user->email,
            'tenant_id' => $tenant->id,
        ]);

        return $this->respondWithToken($user);
    }

    public function login(Request $request): JsonResponse
    {
        Log::info('Auth: login attempt', ['email' => $request->input('email')]);

        try {
            $validated = $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);
        } catch (ValidationException $e) {
            Log::warning('Auth: login validation failed', ['errors' => $e->errors()]);
            throw $e;
        }

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            Log::warning('Auth: login failed - invalid credentials', ['email' => $validated['email']]);
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        Log::info('Auth: login successful', ['user_id' => $user->id, 'email' => $user->email]);

        return $this->respondWithToken($user);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()?->load('tenant');
        Log::info('Auth: me requested', ['user_id' => $user?->id]);

        return response()->json([
            'user' => $user,
        ]);
    }

    public function logout(): JsonResponse
    {
        Log::info('Auth: logout');

        $cookie = cookie()->forget(config('jwt.cookie'));
        return response()->json(['message' => 'Logged out successfully.'])
            ->withCookie($cookie);
    }

    protected function respondWithToken(User $user): JsonResponse
    {
        Log::info('Auth: issuing token and cookie', ['user_id' => $user->id]);

        $tenantId = $user->tenant_id;
        $tenantSlug = $user->tenant?->slug;

        try {
            $token = $this->jwt->issueToken([
                'sub' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'tenant_id' => $tenantId,
                'tenant_slug' => $tenantSlug,
                'role' => $user->role ?? 'member',
            ]);

            $cookiePayload = [
                'sub' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'tenant_id' => $tenantId,
                'role' => $user->role ?? 'member',
            ];

            // Only preload tenant if not already loaded
            if (!$user->relationLoaded('tenant')) {
                $user->load('tenant');
            }

            $cookie = $this->jwt->issueCookie($cookiePayload);
        } catch (\Exception $e) {
            Log::error('Auth: failed to issue token/cookie', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            throw $e;
        }

        Log::info('Auth: token and cookie issued', ['user_id' => $user->id]);

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'subscription' => $user->subscription,
                'tenant_id' => $tenantId,
                'role' => $user->role,
            ],
        ])->withCookie($cookie);
    }
}
