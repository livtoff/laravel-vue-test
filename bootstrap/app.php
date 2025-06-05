<?php

use App\Http\Middleware\GenerateAndSetCspNonce;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\HandleInertiaResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Csp\AddCspHeaders;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            GenerateAndSetCspNonce::class,
            HandleInertiaRequests::class,
            HandleInertiaResponse::class,
            AddCspHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
