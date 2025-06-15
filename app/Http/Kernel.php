<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    // ... existing code ...

    protected $routeMiddleware = [
        // ... existing code ...
        'role' => \App\Http\Middleware\RoleMiddleware::class,
        'admin.only' => \App\Http\Middleware\AdminOnly::class,
    ];
} 