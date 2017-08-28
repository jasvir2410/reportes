<?php

namespace Cosapi\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Cosapi\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \Cosapi\Http\Middleware\VerifyCsrfToken::class,
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth'          => \Cosapi\Http\Middleware\Authenticate::class,
        'admin'         => \Cosapi\Http\Middleware\RoleAdministrator::class,
        'user'          => \Cosapi\Http\Middleware\RoleUser::class,
        'supervisor'    => \Cosapi\Http\Middleware\RoleSupervisor::class,
        'cliente'       => \Cosapi\Http\Middleware\RoleCliente::class,
        'calidad'       => \Cosapi\Http\Middleware\RoleCalidad::class,
        'auth.basic'    => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'guest'         => \Cosapi\Http\Middleware\RedirectIfAuthenticated::class,
    ];
}
