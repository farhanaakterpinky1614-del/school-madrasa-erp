<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Issue / revoke Sanctum personal access tokens (authenticated via existing web session or token).
 */
class TokenController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'abilities' => ['nullable', 'array'],
            'abilities.*' => ['string', 'max:64'],
        ]);

        $token = $request->user()->createToken(
            $data['name'],
            $data['abilities'] ?? ['*'],
        );

        return response()->json([
            'token' => $token->plainTextToken,
            'access_token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'name' => $data['name'],
        ], 201);
    }

    public function destroy(Request $request, int $tokenId): JsonResponse
    {
        $request->user()->tokens()->whereKey($tokenId)->delete();

        return response()->json(['message' => 'Token revoked.']);
    }

    public function index(Request $request): JsonResponse
    {
        $tokens = $request->user()->tokens()->orderByDesc('id')->get([
            'id', 'name', 'abilities', 'last_used_at', 'created_at', 'expires_at',
        ]);

        return response()->json(['data' => $tokens]);
    }
}
