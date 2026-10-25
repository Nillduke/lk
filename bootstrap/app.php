<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // The production origin accepts traffic only from the Sucuri firewall.
        $middleware->trustProxies(at: ['192.88.134.0/23', '185.93.228.0/22', '66.248.200.0/22', '208.109.0.0/22', '2a02:fe80::/29']);
        $middleware->append(\App\Http\Middleware\OptimizeDelivery::class);
        $middleware->encryptCookies(except: ['user_cookie_consent']);
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
