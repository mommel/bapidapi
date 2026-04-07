<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\JwtBlacklist;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

/**
 * Handles user authentication: register, login, logout, refresh, profile, and password reset.
 */
class AuthController extends Controller
{
    /**
     * Register a new user and return JWT tokens.
     *
     * @param RegisterRequest $request Validated registration data
     * @return JsonResponse 200 with access token, or 422 on validation failure
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        /** @var string $token */
        $token = auth('api')->login($user);

        return $this->tokenResponse($token);
    }

    /**
     * Authenticate user and issue JWT tokens.
     *
     * @param LoginRequest $request Validated login credentials
     * @return JsonResponse 200 with access token, or 401 on invalid credentials
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        /** @var string|false $token */
        $token = auth('api')->attempt($credentials);

        if (!$token) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Invalid credentials',
                ],
            ], 401);
        }

        return $this->tokenResponse($token);
    }

    /**
     * Log the user out by blacklisting the current JWT.
     *
     * @return JsonResponse 200 on success, 401 if not authenticated
     */
    public function logout(): JsonResponse
    {
        // Blacklist the current token
        $payload = JWTAuth::parseToken()->getPayload();
        JwtBlacklist::create([
            'jti' => $payload->get('jti'),
            'expires_at' => \Carbon\Carbon::createFromTimestamp($payload->get('exp')),
        ]);

        auth('api')->logout();

        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out',
            'data' => [],
        ]);
    }

    /**
     * Refresh the current JWT token.
     *
     * @return JsonResponse 200 with new access token
     */
    public function refresh(): JsonResponse
    {
        /** @var string $token */
        $token = auth('api')->refresh();

        return $this->tokenResponse($token);
    }

    /**
     * Get the authenticated user's profile.
     *
     * @return JsonResponse 200 with user data, 401 if not authenticated
     */
    public function me(): JsonResponse
    {
        $user = auth('api')->user();

        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at?->toISOString(),
                'updated_at' => $user->updated_at?->toISOString(),
            ],
        ]);
    }

    /**
     * Send a password reset link to the user's email.
     *
     * @param ForgotPasswordRequest $request Validated email
     * @return JsonResponse 200 always (to avoid user enumeration)
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            Password::sendResetLink(
                $request->only('email')
            );
        } catch (\Throwable $e) {
            // Silently fail — we never reveal whether the email exists
            \Illuminate\Support\Facades\Log::warning('Password reset link failed', [
                'error' => $e->getMessage(),
            ]);
        }

        // Always return 200 to prevent user enumeration
        return response()->json([
            'success' => true,
            'message' => 'If an account with that email exists, a reset link has been sent.',
            'data' => [],
        ]);
    }

    /**
     * Reset the user's password using the reset token.
     *
     * @param ResetPasswordRequest $request Validated reset data (token, email, password)
     * @return JsonResponse 200 on success, 400 on invalid/expired token
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'success' => true,
                'message' => 'Password has been reset successfully.',
                'data' => [],
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'PASSWORD_RESET_FAILED',
                'message' => __($status),
            ],
        ], 400);
    }

    /**
     * Build the standard token response envelope.
     *
     * @param string $token The JWT access token
     * @return JsonResponse
     */
    protected function tokenResponse(string $token): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
                'user' => auth('api')->user(),
            ],
        ]);
    }
}
