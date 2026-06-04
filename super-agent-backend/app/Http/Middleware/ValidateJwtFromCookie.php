<?php

namespace App\Http\Middleware;

use App\Services\JwtService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ValidateJwtFromCookie
{
    public function handle(Request $request, Closure $next): Response
    {
        Log::info('JWT middleware: handling request', [
            'path' => $request->path(),
            'method' => $request->method(),
        ]);

        $jwt = app(JwtService::class);

        // Try Authorization header first (Bearer token from Vue frontend)
        $token = null;
        $header = $request->header('Authorization');
        if ($header && str_starts_with($header, 'Bearer ')) {
            $token = substr($header, 7);
            Log::info('JWT middleware: token from Authorization header');
        }

        // Fall back to cookie (for sub-agents)
        if (!$token) {
            $token = $jwt->extractFromCookie();
            if ($token) {
                Log::info('JWT middleware: token from cookie');
            }
        }

        if (!$token) {
            Log::warning('JWT middleware: no token found');
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $payload = $jwt->validateToken($token);
        if (!$payload) {
            Log::warning('JWT middleware: invalid or expired token');
            return response()->json(['message' => 'Invalid or expired token.'], 401);
        }

        Log::info('JWT middleware: token validated', [
            'user_id' => $payload->sub ?? 'unknown',
            'email' => $payload->email ?? 'unknown',
        ]);

        $request->attributes->set('jwt_payload', (array) $payload);
        $request->setUserResolver(function () use ($payload) {
            return \App\Models\User::find($payload->sub);
        });

        return $next($request);
    }
}
