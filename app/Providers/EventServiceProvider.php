<?php

namespace Cosapi\Providers;

use Cosapi\Events\LoginUsers;
use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'Cosapi\Events\SomeEvent' => [
            'Cosapi\Listeners\EventListener',
        ],
        'Cosapi\Events\LoginUsers' => [
            'Cosapi\Listeners\LastLogin',
        ],
    ];

    /**
     * Register any other events for your application.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return void
     */
    public function boot(DispatcherContract $events)
    {
        parent::boot($events);

        $events->listen('auth.login', function ($user, $remember) {
            event(new LoginUsers());
        });
    }
}
