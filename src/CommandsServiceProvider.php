<?php

namespace Wemx\Installer;

use Illuminate\Support\ServiceProvider;
use Wemx\Installer\Commands\PingCommand;
use Wemx\Installer\Commands\WemXInstaller;
use Wemx\Installer\Commands\WemXUpdate;

class CommandsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->commands([
            WemXInstaller::class,
            WemXUpdate::class,
            PingCommand::class,
        ]);
    }

    /**
     * Register the application services.
     */
    public function register()
    {

    }
}
