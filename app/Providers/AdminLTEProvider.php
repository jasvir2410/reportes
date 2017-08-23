<?php

namespace Cosapi\Providers;

use Illuminate\Support\ServiceProvider;

class AdminLTEProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
      $this->publishes([
          __DIR__ . '/../../node_modules/admin-lte/dist' => public_path('../resources/assets/node_modules/admin-lte/dist/'),
      ], 'public');

      $this->publishes([
          __DIR__ . '/../../node_modules/admin-lte/plugins' => public_path('../resources/assets/node_modules/admin-lte/plugins/'),
      ], 'public');

      $this->publishes([
          __DIR__ . '/../../node_modules/admin-lte/bootstrap' => public_path('../resources/assets/node_modules/admin-lte/bootstrap/'),
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
