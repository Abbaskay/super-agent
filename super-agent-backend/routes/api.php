<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\SocialiteController;
use App\Http\Controllers\TenantController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:10,60');
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

Route::get('/auth/{provider}/redirect', [SocialiteController::class, 'redirect'])->middleware('sessions');
Route::get('/auth/{provider}/callback', [SocialiteController::class, 'callback'])->middleware('sessions');

Route::middleware('jwt.cookie')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/chat', [ChatController::class, 'chat']);

    // Tenant management
    Route::middleware('tenant')->group(function () {
        Route::get('/tenant', [TenantController::class, 'show']);
        Route::put('/tenant', [TenantController::class, 'update']);
        Route::get('/tenant/members', [TenantController::class, 'members']);
        Route::post('/tenant/invite', [TenantController::class, 'invite']);
        Route::delete('/tenant/members/{user}', [TenantController::class, 'removeMember']);
    });
});

// Sub-agent JWT validation endpoint
Route::post('/validate-token', function (\Illuminate\Http\Request $request) {
    Log::info('Route: validate-token called');

    $jwt = app(\App\Services\JwtService::class);
    $token = $request->input('token') ?? $jwt->extractFromCookie();
    if (!$token) {
        Log::warning('Route: validate-token - no token provided');
        return response()->json(['valid' => false, 'message' => 'No token provided'], 401);
    }
    $payload = $jwt->validateToken($token);
    if (!$payload) {
        Log::warning('Route: validate-token - invalid token');
        return response()->json(['valid' => false, 'message' => 'Invalid token'], 401);
    }

    Log::info('Route: validate-token - valid', ['user_id' => $payload->sub ?? 'unknown']);

    return response()->json(['valid' => true, 'user' => $payload]);
});


