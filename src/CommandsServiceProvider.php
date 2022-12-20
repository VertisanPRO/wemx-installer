<?php

namespace Billing\Commands;

use Illuminate\Support\ServiceProvider;
use Billing\Commands\BillingCommands;

class CommandsServiceProvider extends ServiceProvider
{
  /**
   * Bootstrap the application services.
   */
  public function boot()
  {
    /*
         * Optional methods to load your package assets
         */
    // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'commands');
    // $this->loadViewsFrom(__DIR__.'/../resources/views', 'commands');
    // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    // $this->loadRoutesFrom(__DIR__.'/routes.php');

    if ($this->app->runningInConsole()) {
      $this->publishes([
        __DIR__ . '/../config/config.php' => config_path('commands.php'),
      ], 'config');

      // Publishing the views.
      /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/commands'),
            ], 'views');*/

      // Publishing assets.
      /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/commands'),
            ], 'assets');*/

      // Publishing the translation files.
      /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/commands'),
            ], 'lang');*/

      // Registering package commands.
      $this->commands([BillingCommands::class]);
    }
  }

  /**
   * Register the application services.
   */
  public function register()
  {
    // Automatically apply the package configuration
    $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'commands');

    // Register the main class to use with the facade
    $this->app->singleton('commands', function () {
      return new Commands;
    });
  }
}
