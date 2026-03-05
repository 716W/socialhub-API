<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $json = fn (array $body, int $status) => response()->json(
            array_merge($body, ['timestamp' => now()->toIso8601String()]),
            $status
        );

        // 422 – Validation errors (FormRequest / $request->validate())
        $exceptions->render(function (ValidationException $e, Request $request) use ($json) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return $json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        // 401 – Unauthenticated (missing / expired token)
        $exceptions->render(function (AuthenticationException $e, Request $request) use ($json) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return $json([
                    'success' => false,
                    'message' => 'Unauthenticated. Please login first.',
                    'errors'  => null,
                ], 401);
            }
        });

        // 403 – Unauthorized (Gate / Policy)
        $exceptions->render(function (AuthorizationException $e, Request $request) use ($json) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return $json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'You are not authorized to perform this action.',
                    'errors'  => null,
                ], 403);
            }
        });

        // 404 – Model not found (findOrFail / route model binding)
        $exceptions->render(function (ModelNotFoundException $e, Request $request) use ($json) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $model = class_basename($e->getModel());
                return $json([
                    'success' => false,
                    'message' => "{$model} not found.",
                    'errors'  => null,
                ], 404);
            }
        });

        // 4xx / 5xx – Generic HTTP exceptions (abort(), 404 route miss, 405, 429 …)
        $exceptions->render(function (HttpException $e, Request $request) use ($json) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $message = $e->getMessage()
                    ?: (Response::$statusTexts[$e->getStatusCode()] ?? 'An error occurred.');
                return $json([
                    'success' => false,
                    'message' => $message,
                    'errors'  => null,
                ], $e->getStatusCode());
            }
        });

        // 500 – Unexpected exceptions
        $exceptions->render(function (\Throwable $e, Request $request) use ($json) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return $json([
                    'success' => false,
                    'message' => app()->isProduction()
                        ? 'An unexpected error occurred. Please try again later.'
                        : $e->getMessage(),
                    'errors'  => null,
                ], 500);
            }
        });

    })->create();

