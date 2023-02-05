<?php

namespace Wemx\Installer;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Wemx\Installer\Commands\BackupCommand;
use Wemx\Installer\Commands\CreateMySQLUser;
use Wemx\Installer\Commands\DeleteMySQLUser;
use Wemx\Installer\Commands\FixCommand;
use Wemx\Installer\Commands\HelpCommand;
use Wemx\Installer\Commands\InstallCommand;
use Wemx\Installer\Commands\InstallphpMyAdmin;
use Wemx\Installer\Commands\LicenseCommand;
use Wemx\Installer\Commands\UninstallCommand;
use Wemx\Installer\Commands\VersionCommand;
use Wemx\Installer\Commands\YarnCommand;
use Wemx\Installer\FileEditor;

class CommandsServiceProvider extends ServiceProvider
{

    private $wemx_backup;
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                HelpCommand::class,
                UninstallCommand::class,
                FixCommand::class,
                YarnCommand::class,
                InstallphpMyAdmin::class,
                CreateMySQLUser::class,
                DeleteMySQLUser::class,
                LicenseCommand::class,
                BackupCommand::class,
                VersionCommand::class
            ]);
        }

        $this->publishes([__DIR__ . '/../config/wemx-backup.php' => config_path('wemx-backup.php')], 'wemx-backup');

        if (config('wemx-backup.autobackup')) {
            $this->app->booted(function () {
                $schedule = app(Schedule::class);
                $schedule->command('backup --action=create --type=all')->cron(config('wemx-backup.autobackup_cron'));

            });
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/aliases.php', 'app.aliases');
        $this->mergeConfig();

    }

    private function mergeConfig()
    {
        if (file_exists(config_path('wemx-backup.php'))) {
            $this->wemx_backup = include __DIR__ . '/../config/wemx-backup.php';
            $wemx_backup = config('wemx-backup');
            foreach ($this->wemx_backup as $key => $value) {
                if (isset($wemx_backup[$key])) {
                    continue;
                }
                FileEditor::appendAfter(config_path('wemx-backup.php'), 'return [', "    '{$key}' => " . json_encode($value) . ",");
            }
        }
        return;
    }
}