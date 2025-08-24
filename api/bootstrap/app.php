<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
        $middleware->trustProxies(at: [
            '127.0.0.1',
            '192.168.0.0/16',
            '172.16.0.0/12',
            '10.0.0.0/8',
        ]);
        $middleware->append(\App\Http\Middleware\LocalizationResponse::class);
        $middleware->append(\App\Http\Middleware\ProfileJsonResponse::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
