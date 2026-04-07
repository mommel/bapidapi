<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        apiPrefix: 'api/v1',
        commands: __DIR__ . '/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            \App\Http\Middleware\ForceJsonResponse::class,
        ]);

        $middleware->api(append: [
            \App\Http\Middleware\SecurityHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // Ensure all API exceptions return JSON (never HTML)
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'NOT_FOUND',
                        'message' => 'The requested resource was not found.',
                    ],
                ], 404);
            }
        });

        $exceptions->render(function (TooManyRequestsHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'TOO_MANY_REQUESTS',
                        'message' => 'Rate limit exceeded. Please wait before retrying.',
                    ],
                ], 429);
            }
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'VALIDATION_ERROR',
                        'message' => 'Validation failed',
                        'details' => $e->errors(),
                    ],
                ], 422);
            }
        });

        $exceptions->render(function (HttpException $e, Request $request) {
            if ($request->is('api/*')) {
                $code = match ($e->getStatusCode()) {
                    401 => 'UNAUTHORIZED',
                    403 => 'FORBIDDEN',
                    405 => 'METHOD_NOT_ALLOWED',
                    409 => 'CONFLICT',
                    default => 'HTTP_ERROR',
                };

                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => $code,
                        'message' => $e->getMessage() ?: 'An error occurred.',
                    ],
                ], $e->getStatusCode());
            }
        });
    })->create();
