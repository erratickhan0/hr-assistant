<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreApiTokenRequest;
use App\Http\Resources\Api\OrganizationResource;
use App\Http\Resources\Api\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccessTokenController extends Controller
{
    public function store(StoreApiTokenRequest $request): JsonResponse
    {
        $user = User::query()
            ->where('email', $request->validated('email'))
            ->whereHas('organization', function ($query) use ($request): void {
                $query->where('slug', $request->validated('organization_slug'));
            })
            ->with('organization')
            ->first();

        if ($user === null || ! Hash::check($request->validated('password'), $user->password)) {
            return response()->json([
                'message' => __('These credentials do not match our records.'),
            ], 422);
        }

        $deviceName = $request->validated('device_name') ?: 'api-client';
        $plainTextToken = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'token' => $plainTextToken,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
            'organization' => new OrganizationResource($user->organization),
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();
        if ($token !== null) {
            $token->delete();
        }

        return response()->json(null, 204);
    }
}
