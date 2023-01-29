<?php

namespace Wemx\Installer;

use Illuminate\Support\ServiceProvider;
use Wemx\Installer\Commands\InstallCommand;
use Wemx\Installer\Commands\HelpCommand;
use Wemx\Installer\Commands\UninstallCommand;
use Wemx\Installer\Commands\FixCommand;
use Wemx\Installer\Commands\YarnCommand;
use Wemx\Installer\Commands\InstallphpMyAdmin;
use Wemx\Installer\Commands\CreateMySQLUser;
use Wemx\Installer\Commands\DeleteMySQLUser;
use Wemx\Installer\Commands\LicenseCommand;
use Wemx\Installer\Commands\BackupCommand;

class CommandsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([InstallCommand::class, HelpCommand::class, UninstallCommand::class, FixCommand::class, YarnCommand::class, InstallphpMyAdmin::class, CreateMySQLUser::class, DeleteMySQLUser::class, LicenseCommand::class, BackupCommand::class]);
        }

        $this->publishes([__DIR__ . '/../config/wemx_backup.php' => config_path('wemx_backup.php')], 'config');
        exec('php artisan vendor:publish --provider="Wemx\Installer\CommandsServiceProvider"');
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/wemx_backup.php', 'wemx_backup');
        $this->mergeConfigFrom(__DIR__ . '/../config/aliases.php', 'app.aliases');
    }
}
