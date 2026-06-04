<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    Log::info('Route: welcome page');
    return view('welcome');
});

// SSO redirect: pass JWT to sub-agents via URL (web route, not API)
Route::get('/sso/{agent}', function (string $agent, \Illuminate\Http\Request $request) {
    Log::info('SSO: redirect requested', ['agent' => $agent]);

    $urls = [
        'docs' => env('DOCS_AGENT_URL', 'http://localhost:8002'),
        'slides' => env('SLIDES_AGENT_URL', 'http://localhost:8003'),
        'fc' => env('FC_BACKEND_URL', 'http://localhost:8001'),
        'factcheck' => env('FC_BACKEND_URL', 'http://localhost:8001'),
        'excel' => env('EXCEL_AGENT_URL', 'http://localhost:8004'),
    ];

    $redirectTo = $request->query('redirect', '');

    $jwt = app(\App\Services\JwtService::class);
    $payload = $request->get('jwt_payload');
    if ($payload) {
        $token = $jwt->issueToken((array) $payload);
    } else {
        $cookieToken = $jwt->extractFromCookie();
        $cookiePayload = $cookieToken ? $jwt->validateToken($cookieToken) : null;
        $token = $cookieToken;
        // Ensure tenant_id is in the re-issued token
        if ($cookiePayload && !empty($cookiePayload->tenant_id)) {
            $token = $jwt->issueToken([
                'sub' => $cookiePayload->sub,
                'email' => $cookiePayload->email ?? '',
                'name' => $cookiePayload->name ?? '',
                'tenant_id' => $cookiePayload->tenant_id,
                'role' => $cookiePayload->role ?? 'member',
            ]);
        }
    }
    if (!$token) {
        Log::warning('SSO: no token available, redirecting to login');
        $loginUrl = env('FRONTEND_URL', 'http://localhost:5173') . '/login';
        if ($redirectTo) {
            $loginUrl .= '?redirect=' . urlencode($redirectTo);
        }
        return redirect($loginUrl);
    }

    $target = rtrim($urls[$agent] ?? '/', '/');
    $ssoUrl = $target . '/api/auth/sso?token=' . $token;
    if ($redirectTo) {
        $ssoUrl .= '&redirect=' . urlencode($redirectTo);
    }

    Log::info('SSO: redirecting to agent', [
        'agent' => $agent,
        'target_url' => $target,
    ]);

    return redirect($ssoUrl);
})->middleware('jwt.cookie');
