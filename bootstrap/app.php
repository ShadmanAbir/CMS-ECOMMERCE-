<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);

        // Exclude payment gateway callback routes from CSRF verification
        $middleware->validateCsrfTokens(except: [
            'payment/callback/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\App\Exceptions\InsufficientStockException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
        });

        $exceptions->render(function (\App\Exceptions\PaymentFailedException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 402);
            }
        });

        $exceptions->render(function (\App\Exceptions\OrderCancellationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
            }
        });
    })
    ->withProviders([
        \App\Providers\AppServiceProvider::class,
        \App\Providers\ThemeServiceProvider::class,
        \App\Providers\ViewComposerServiceProvider::class,
    ])
    ->create();
