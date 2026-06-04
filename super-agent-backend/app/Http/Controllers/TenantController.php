<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $tenantId = $request->input('current_tenant_id');
        if (!$tenantId) {
            return response()->json(['message' => 'No tenant context.'], 403);
        }

        $tenant = Tenant::with('users')->find($tenantId);
        if (!$tenant) {
            return response()->json(['message' => 'Tenant not found.'], 404);
        }

        return response()->json(['tenant' => $tenant]);
    }

    public function update(Request $request): JsonResponse
    {
        $tenantId = $request->input('current_tenant_id');
        if (!$tenantId) {
            return response()->json(['message' => 'No tenant context.'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'settings' => 'sometimes|json',
        ]);

        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            return response()->json(['message' => 'Tenant not found.'], 404);
        }

        if (isset($validated['name'])) {
            $tenant->name = $validated['name'];
            $tenant->slug = Str::slug($validated['name']) . '-' . Str::random(6);
        }
        if (isset($validated['settings'])) {
            $tenant->settings = json_decode($validated['settings'], true);
        }
        $tenant->save();

        Log::info('Tenant: updated', ['tenant_id' => $tenantId]);

        return response()->json(['tenant' => $tenant]);
    }

    public function members(Request $request): JsonResponse
    {
        $tenantId = $request->input('current_tenant_id');
        if (!$tenantId) {
            return response()->json(['message' => 'No tenant context.'], 403);
        }

        $members = User::where('tenant_id', $tenantId)
            ->select('id', 'name', 'email', 'role', 'created_at')
            ->get();

        return response()->json(['members' => $members]);
    }

    public function invite(Request $request): JsonResponse
    {
        $tenantId = $request->input('current_tenant_id');
        $role = $request->input('current_tenant_role');
        if (!$tenantId) {
            return response()->json(['message' => 'No tenant context.'], 403);
        }

        // Only owner/admin can invite
        if (!in_array($role, ['owner', 'admin'])) {
            return response()->json(['message' => 'Only tenant owners can invite members.'], 403);
        }

        $validated = $request->validate([
            'email' => 'required|email',
            'name' => 'required|string|max:255',
            'role' => 'sometimes|string|in:admin,member',
        ]);

        // Check if user already exists in this tenant
        $existing = User::where('email', $validated['email'])->first();
        if ($existing && $existing->tenant_id === $tenantId) {
            return response()->json(['message' => 'User is already a member of this tenant.'], 409);
        }

        $member = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => null,
            'tenant_id' => $tenantId,
            'role' => $validated['role'] ?? 'member',
        ]);

        Log::info('Tenant: member invited', [
            'tenant_id' => $tenantId,
            'user_id' => $member->id,
        ]);

        return response()->json(['member' => $member], 201);
    }

    public function removeMember(Request $request, string $userId): JsonResponse
    {
        $tenantId = $request->input('current_tenant_id');
        $role = $request->input('current_tenant_role');

        if (!$tenantId) {
            return response()->json(['message' => 'No tenant context.'], 403);
        }
        if (!in_array($role, ['owner', 'admin'])) {
            return response()->json(['message' => 'Only tenant owners can remove members.'], 403);
        }

        $member = User::where('tenant_id', $tenantId)->find($userId);
        if (!$member) {
            return response()->json(['message' => 'Member not found.'], 404);
        }

        $member->tenant_id = null;
        $member->role = null;
        $member->save();

        Log::info('Tenant: member removed', [
            'tenant_id' => $tenantId,
            'user_id' => $userId,
        ]);

        return response()->json(['message' => 'Member removed.']);
    }
}
