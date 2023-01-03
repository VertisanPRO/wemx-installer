<?php

namespace Billing\Commands;

use Illuminate\Support\ServiceProvider;
use Billing\Commands\Commands\LicenseCommand;
use Billing\Commands\Commands\InstallCommand;
use Billing\Commands\Commands\HelpCommand;
use Billing\Commands\Commands\CheckVersionCommand;
use Billing\Commands\Commands\UninstallCommand;
use Billing\Commands\Commands\FixCommand;
use Billing\Commands\Commands\YarnCommand;

class CommandsServiceProvider extends ServiceProvider
{
  /**
   * Bootstrap the application services.
   */
  public function boot()
  {

    if ($this->app->runningInConsole()) {
      // Registering package commands.
      $this->commands([InstallCommand::class, HelpCommand::class, UninstallCommand::class, CheckVersionCommand::class, FixCommand::class, LicenseCommand::class, YarnCommand::class]);
    }
  }

  /**
   * Register the application services.
   */
  public function register()
  {
    // Automatically apply the package configuration
    $this->mergeConfigFrom(
      __DIR__ . '/../config/aliases.php',
      'app.aliases'
    );
  }
}
