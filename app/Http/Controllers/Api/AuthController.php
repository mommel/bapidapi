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
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;

/**
 * Handles user authentication: register, login, logout, refresh, profile, and password reset.
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/auth/register",
     *     operationId="authRegister",
     *     tags={"Auth"},
     *     summary="Register a new user",
     *     description="Creates a new user account and returns a JWT access token.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *
     *             @OA\Property(property="name", type="string", example="Jan Kowalski"),
     *             @OA\Property(property="email", type="string", format="email", example="jan@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secret123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="secret123")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Registered successfully",
     *
     *         @OA\JsonContent(ref="#/components/schemas/TokenResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     )
     * )
     *
     * @param  RegisterRequest  $request  Validated registration data
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

        $token = $this->jwtGuard()->login($user);

        return $this->tokenResponse($token);
    }

    /**
     * @OA\Post(
     *     path="/auth/login",
     *     operationId="authLogin",
     *     tags={"Auth"},
     *     summary="Log in and obtain a JWT",
     *     description="Authenticates the user and returns an access token.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"email","password"},
     *
     *             @OA\Property(property="email", type="string", format="email", example="jan@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secret123")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Authenticated successfully",
     *
     *         @OA\JsonContent(ref="#/components/schemas/TokenResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     )
     * )
     *
     * @param  LoginRequest  $request  Validated login credentials
     * @return JsonResponse 200 with access token, or 401 on invalid credentials
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        /** @var string|false $token */
        $token = $this->jwtGuard()->attempt($credentials);

        if (! $token) {
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
     * @OA\Post(
     *     path="/auth/logout",
     *     operationId="authLogout",
     *     tags={"Auth"},
     *     summary="Log out the authenticated user",
     *     description="Blacklists the current JWT and invalidates the session.",
     *     security={{"BearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Logged out successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Successfully logged out"),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     *
     * @return JsonResponse 200 on success, 401 if not authenticated
     */
    public function logout(): JsonResponse
    {
        // Blacklist the current token
        $payload = JWTAuth::parseToken()->getPayload();
        JwtBlacklist::create([
            'jti' => $payload->get('jti'),
            'expires_at' => Carbon::createFromTimestamp($payload->get('exp')),
        ]);

        auth('api')->logout();

        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out',
            'data' => [],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/auth/refresh",
     *     operationId="authRefresh",
     *     tags={"Auth"},
     *     summary="Refresh the JWT access token",
     *     description="Issues a new access token using the current valid token.",
     *     security={{"BearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Token refreshed successfully",
     *
     *         @OA\JsonContent(ref="#/components/schemas/TokenResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated or token expired",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     *
     * @return JsonResponse 200 with new access token
     */
    public function refresh(): JsonResponse
    {
        $token = $this->jwtGuard()->refresh();

        return $this->tokenResponse($token);
    }

    /**
     * @OA\Get(
     *     path="/auth/me",
     *     operationId="authMe",
     *     tags={"Auth"},
     *     summary="Get the authenticated user's profile",
     *     security={{"BearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="User profile",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="OK"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="name", type="string", example="Jan Kowalski"),
     *                 @OA\Property(property="email", type="string", format="email"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
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
     * @OA\Post(
     *     path="/auth/password/forgot",
     *     operationId="authForgotPassword",
     *     tags={"Auth"},
     *     summary="Request a password reset link",
     *     description="Sends a reset link to the provided email. Always returns 200 to prevent user enumeration.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"email"},
     *
     *             @OA\Property(property="email", type="string", format="email", example="jan@example.com")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Reset link sent (or silently ignored if email not found)",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="If an account with that email exists, a reset link has been sent."),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     )
     * )
     *
     * @param  ForgotPasswordRequest  $request  Validated email
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
            Log::warning('Password reset link failed', [
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
     * @OA\Post(
     *     path="/auth/password/reset",
     *     operationId="authResetPassword",
     *     tags={"Auth"},
     *     summary="Reset the user's password",
     *     description="Resets the password using the token received by email.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"email","password","password_confirmation","token"},
     *
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string", format="password"),
     *             @OA\Property(property="password_confirmation", type="string", format="password"),
     *             @OA\Property(property="token", type="string")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Password reset successfully",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Password has been reset successfully."),
     *             @OA\Property(property="data", type="array", @OA\Items())
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Invalid or expired token",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     )
     * )
     *
     * @param  ResetPasswordRequest  $request  Validated reset data (token, email, password)
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
     * Return the JWT guard cast to its concrete type for static analysis.
     */
    private function jwtGuard(): JWTGuard
    {
        /** @var JWTGuard $guard */
        $guard = auth('api');

        return $guard;
    }

    /**
     * Build the standard token response envelope.
     *
     * @param  string  $token  The JWT access token
     */
    protected function tokenResponse(string $token): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'OK',
            'data' => [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => $this->jwtGuard()->factory()->getTTL() * 60,
                'user' => $this->jwtGuard()->user(),
            ],
        ]);
    }
}
