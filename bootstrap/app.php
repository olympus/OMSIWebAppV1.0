<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'apiauth' => \App\Http\Middleware\ApiAuth::class,
            'ipcheck' => \App\Http\Middleware\RestrictIpAddresses::class,
            'isallowed' => \App\Http\Middleware\IsAllowed::class,
            'prevent-back-history' => \App\Http\Middleware\PreventBackHistory::class,
            'is_expired' => \App\Http\Middleware\is_expired::class,
            'XSS' => \App\Http\Middleware\XSS::class,
            'jwt.verify' => \App\Http\Middleware\JwtMiddleware::class,
//            'jwt.auth' => \Tymon\JWTAuth\Middleware\GetUserFromToken::class,
//            'jwt.refresh' => \Tymon\JWTAuth\Middleware\RefreshToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
