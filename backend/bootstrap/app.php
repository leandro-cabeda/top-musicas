<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;


return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        \SwaggerLume\ServiceProvider::class,
    ])
    ->withRouting(
        web: [__DIR__ . '/../routes/web.php', __DIR__ . '/../routes/api.php'],
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        /*
            Ele irá ser usado em todas rotas
            globalmente
        */
        $middleware->append(
            \App\Http\Middleware\CorsMiddleware::class
        );

        /*
            Ele só será usado quando for explicitamente chamado nas rotas
            onde contem admin e nisso não irá aplicar em todas rotas
            globalmente
        */
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
