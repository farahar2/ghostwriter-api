<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RegisterFormRequest;
use App\Http\Requests\Api\LoginFormRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    /**
     * @group Authentication
     *
     * Register a new user account.
     *
     * @bodyParam name string required The user's full name. Example: John Doe
     * @bodyParam email string required The user's email address. Example: john@example.com
     * @bodyParam password string required The user's password (min 8 chars, mixed case, numbers). Example: Password123
     * @bodyParam password_confirmation string required Must match password. Example: Password123
     *
     * @response 201 {
     *     "message": "Account created successfully",
     *     "data": {
     *         "user": {
     *             "id": 1,
     *             "name": "John Doe",
     *             "email": "john@example.com",
     *             "created_at": "08/07/2026 14:30"
     *         },
     *         "token": "1|abc123..."
     *     }
     * }
     * @responseField data.user Object The created user.
     * @responseField data.token String The Bearer token for authentication.
     *
     * @response 422 {
     *     "message": "The given data was invalid.",
     *     "errors": {
     *         "email": ["This email address is already registered."],
     *         "password": ["The password confirmation does not match."]
     *     }
     * }
     *
     * Register a new user and return a Bearer token.
     */
    public function register(RegisterFormRequest $request): JsonResponse
    {
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => $request->password,
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Account created successfully',
            'data'    => [
                'user'  => UserResource::make($user),
                'token' => $token,
            ],
        ], 201);
    }

    /**
     * @group Authentication
     *
     * Authenticate an existing user.
     *
     * @bodyParam email string required The user's email address. Example: john@example.com
     * @bodyParam password string required The user's password. Example: Password123
     *
     * @response 200 {
     *     "message": "Login successful",
     *     "data": {
     *         "user": {
     *             "id": 1,
     *             "name": "John Doe",
     *             "email": "john@example.com",
     *             "created_at": "08/07/2026 14:30"
     *         },
     *         "token": "2|def456..."
     *     }
     * }
     * @responseField data.user Object The authenticated user.
     * @responseField data.token String The Bearer token for authentication.
     *
     * @response 401 {
     *     "message": "Invalid credentials"
     * }
     *
     * @response 422 {
     *     "message": "The given data was invalid.",
     *     "errors": {
     *         "email": ["The email address is required."],
     *         "password": ["The password is required."]
     *     }
     * }
     *
     * Authenticate user and return a Bearer token.
     */
    public function login(LoginFormRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'data'    => [
                'user'  => UserResource::make($user),
                'token' => $token,
            ],
        ], 200);
    }

    /**
     * @group Authentication
     *
     * Revoke the current access token.
     *
     * @response 200 {
     *     "message": "Logged out successfully"
     * }
     *
     * @response 401 {
     *     "message": "Unauthenticated."
     * }
     *
     * Revoke the current access token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ], 200);
    }
}