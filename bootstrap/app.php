<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SetTeamUrlDefaults;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        /*
         * AddLinkHeadersForPreloadedAssets is deliberately absent. It emits a
         * Link: rel=preload header naming every Vite asset on the page, which
         * runs to about 3 KB once the font faces are listed. Our host proxies
         * PHP through nginx with the stock 4 KB proxy_buffer_size, and the
         * login page cleared that on its own (4170 bytes of headers), so nginx
         * answered 500 -- "upstream sent too big header" -- while lighter
         * pages squeaked through. Preloading is not worth an unreachable CMS.
         */
        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            SetTeamUrlDefaults::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        /*
         * The staff areas answer 404 for signed-out visitors instead of
         * redirecting them to the login form.
         *
         * The login lives at a deliberately obscure path (FORTIFY_PREFIX). A
         * redirect from a guessable URL like /admin would hand that path to
         * anyone who typed it, along with confirmation that a CMS is here.
         *
         * This is done on the authentication failure rather than in middleware:
         * Laravel's middleware priority runs `auth` ahead of the whole web
         * group, so no middleware of ours can answer first.
         */
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('admin', 'admin/*', 'settings', 'settings/*')) {
                abort(404);
            }
        });
    })->create();
