<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (NotFoundHttpException $exception, Request $request) {
            if (!$request->is('api/*') && !$request->expectsJson()) {
                return NULL;
            }

            $previousException = $exception->getPrevious();

            if ($previousException instanceof ModelNotFoundException) {
                $model = class_basename(
                    $previousException->getModel()
                );

                return response()->json([
                    'status' => 404,
                    'message' => $model . ' Not Found',
                    'data' => [],
                ], 404);
            }

            return response()->json([
                'status' => 404,
                'message' => 'Endpoint Not Found',
                'data' => [],
            ], 404);
        });

        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'status' => 401,
                    'message' => 'Unauthenticated',
                    'data' => [],
                ], 401);
            }

            return NULL;
        });

        $exceptions->shouldRenderJsonWhen(
            fn(Request $request) => $request->is('api/*'),
        );
    })->create();
