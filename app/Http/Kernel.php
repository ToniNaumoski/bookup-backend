<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    // Global HTTP middleware stack
    protected $middleware = [
        // ...
    ];

    // Route middleware groups
    protected $middlewareGroups = [
        'web' => [
            // ...
        ],

        'api' => [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    // Individual route middleware
    protected $middlewareAliases = [
        // 'super_admin' => \App\Http\Middleware\SuperAdminMiddleware::class,
    ];
}
