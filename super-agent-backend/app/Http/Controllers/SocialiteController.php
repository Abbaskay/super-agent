<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use App\Services\JwtService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function __construct(
        protected JwtService $jwt
    ) {}

    public function redirect(string $provider): RedirectResponse
    {
        Log::info('Socialite: redirect initiated', ['provider' => $provider]);
        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider, Request $request): JsonResponse|RedirectResponse
    {
        Log::info('Socialite: callback received', ['provider' => $provider]);

        try {
            $socialUser = Socialite::driver($provider)->user();
            Log::info('Socialite: user retrieved from provider', [
                'provider' => $provider,
                'social_id' => $socialUser->getId(),
                'email' => $socialUser->getEmail(),
            ]);
        } catch (\Exception $e) {
            Log::error('Socialite: authentication failed', [
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Authentication failed: ' . $e->getMessage()], 401);
        }

        // Find or create user — assign tenant for new users
        $user = User::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        $wasCreated = false;
        if (!$user) {
            $tenant = Tenant::create([
                'name' => $socialUser->getName() . "'s Workspace",
                'slug' => Str::slug($socialUser->getName()) . '-' . Str::random(6),
            ]);

            $user = User::create([
                'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
                'email' => $socialUser->getEmail() ?? $socialUser->getId() . '@' . $provider . '.com',
                'avatar' => $socialUser->getAvatar(),
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'provider_token' => $socialUser->token,
                'password' => null,
                'tenant_id' => $tenant->id,
                'role' => 'owner',
            ]);
            $wasCreated = true;
        } else {
            // Update existing user's info
            $user->update([
                'name' => $socialUser->getName() ?? $user->name,
                'avatar' => $socialUser->getAvatar() ?? $user->avatar,
                'provider_token' => $socialUser->token,
            ]);
        }

        Log::info('Socialite: user upserted', [
            'user_id' => $user->id,
            'was_created' => $wasCreated,
            'provider' => $provider,
            'tenant_id' => $user->tenant_id,
        ]);

        $tokenPayload = [
            'sub' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'tenant_id' => $user->tenant_id,
            'role' => $user->role ?? 'member',
        ];

        $token = $this->jwt->issueToken($tokenPayload);
        $cookie = $this->jwt->issueCookie($tokenPayload);

        $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');

        Log::info('Socialite: redirecting to frontend', ['frontend_url' => $frontendUrl, 'user_id' => $user->id]);

        return redirect($frontendUrl . '/auth/callback?token=' . $token . '&user=' . urlencode(json_encode([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar,
            'subscription' => $user->subscription,
        ])))->withCookie($cookie);
    }
}
