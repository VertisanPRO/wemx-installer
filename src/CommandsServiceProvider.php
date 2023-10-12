<?php

namespace Wemx\Installer;

use Illuminate\Support\ServiceProvider;
use Wemx\Installer\Commands\PingCommand;
use Wemx\Installer\Commands\WemXInstaller;
use Wemx\Installer\Commands\WemXUpdate;
use Wemx\Installer\Http\Middleware\CheckAppInstalled;

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
            PingCommand::class,
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
        // add commands
    }
}
