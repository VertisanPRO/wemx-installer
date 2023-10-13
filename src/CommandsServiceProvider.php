<?php

namespace Wemx\Installer;

use Illuminate\Support\ServiceProvider;
use Wemx\Installer\Commands\PingCommand;
use Wemx\Installer\Commands\Setup\DatabaseSettingsCommand;
use Wemx\Installer\Commands\Setup\SetupApacheCommand;
use Wemx\Installer\Commands\Setup\SetupCommand;
use Wemx\Installer\Commands\Setup\SetupDatabaseCommand;
use Wemx\Installer\Commands\Setup\SetupNginxCommand;
use Wemx\Installer\Commands\Setup\SetWebChownCommand;
use Wemx\Installer\Commands\WemXInstaller;
use Wemx\Installer\Commands\WemXUpdate;
use Wemx\Installer\Commands\QueueCommands;
use Wemx\Installer\Middleware\CheckAppInstalled;

use Illuminate\Console\Events\Scheduling;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Events\Dispatcher;

class CommandsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(Dispatcher $events)
    {
        $this->commands([
            WemXInstaller::class,
            WemXUpdate::class,
            QueueCommands::class,
            WemXUpdate::class,
            DatabaseSettingsCommand::class,
            SetupCommand::class,
            SetupDatabaseCommand::class,
            SetupNginxCommand::class,
            SetupApacheCommand::class,
            SetWebChownCommand::class,
        ]);

        $this->app['router']->aliasMiddleware('app_installed', CheckAppInstalled::class);
        $this->loadRoutesFrom(__DIR__ . '/routes.php');
        $this->loadViewsFrom(__DIR__.'/Views', 'installer');

        $events->listen(Scheduling::class, function (Scheduling $event) {
            $this->schedule($event->schedule);
        });
    }

    /**
     * Register the application services.
     */
    public function register()
    {

    }

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('queue:commands')->everyFiveSeconds()->withoutOverlapping();
    }
}
