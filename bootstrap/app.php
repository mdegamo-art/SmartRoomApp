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
        // ── API auth model ──
        // The mobile app authenticates with Sanctum personal access tokens
        // (Bearer header), NOT cookie-based SPA auth. Do NOT enable
        // statefulApi()/EnsureFrontendRequestsAreStateful here: doing so makes
        // any /api/* request whose Origin/Referer matches a stateful domain
        // (e.g. the LAN IP set as APP_URL) run the web session + CSRF
        // middleware, so token logins fail with "CSRF token mismatch" (419).
        // Keeping /api/* stateless lets the mobile app log in over the LAN.

        // ── CORS — allow React Native app to call the API ──
        $middleware->append(\Illuminate\Http\Middleware\HandleCors::class);

        // ── Web middleware (session, csrf, auth) ──
        $middleware->web(append: [
            \Illuminate\Session\Middleware\AuthenticateSession::class,
        ]);

        // ── Aliases ──
        $middleware->alias([
            'auth'     => \App\Http\Middleware\Authenticate::class,
            'guest'    => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            'admin'    => \App\Http\Middleware\EnsureAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();