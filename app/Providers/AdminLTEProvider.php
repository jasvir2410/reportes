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
          __DIR__ . '/../../vendor/almasaeed2010/adminlte/dist' => public_path('../resources/assets/adminlte/dist/'),
      ], 'public');

      $this->publishes([
          __DIR__ . '/../../vendor/almasaeed2010/adminlte/bower_components/bootstrap/dist' => public_path('../resources/assets/adminlte/plugins/bootstrap/dist/'),
      ], 'public');

      $this->publishes([
          __DIR__ . '/../../vendor/almasaeed2010/adminlte/bower_components/font-awesome/css' => public_path('../resources/assets/adminlte/plugins/font-awesome/css/'),
      ], 'public');

      $this->publishes([
          __DIR__ . '/../../vendor/almasaeed2010/adminlte/bower_components/font-awesome/fonts' => public_path('../resources/assets/adminlte/plugins/font-awesome/fonts/'),
      ], 'public');

      $this->publishes([
          __DIR__ . '/../../vendor/almasaeed2010/adminlte/bower_components/Ionicons/css' => public_path('../resources/assets/adminlte/plugins/Ionicons/css/'),
      ], 'public');

      $this->publishes([
          __DIR__ . '/../../vendor/almasaeed2010/adminlte/bower_components/Ionicons/fonts' => public_path('../resources/assets/adminlte/plugins/Ionicons/fonts/'),
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
