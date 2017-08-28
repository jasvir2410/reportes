<?php

namespace Cosapi\Providers;

use Illuminate\Support\ServiceProvider;

class SocketioServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
      $this->publishes([
          __DIR__ . '/../../node_modules/socket.io/lib' => public_path('../resources/assets/node_modules/socket.io'),
      ], 'public');

      $this->publishes([
          __DIR__ . '/../../node_modules/socket.io-client/dist' => public_path('../resources/assets/node_modules/socket.io-client'),
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
