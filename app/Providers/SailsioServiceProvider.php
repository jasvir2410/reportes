<?php

namespace Cosapi\Providers;

use Illuminate\Support\ServiceProvider;

class SailsioServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
      $this->publishes([
          __DIR__ . '/../../node_modules/sails.io.js/dist' => public_path('../resources/assets/node_modules/sails.io'),
      ], 'public');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
