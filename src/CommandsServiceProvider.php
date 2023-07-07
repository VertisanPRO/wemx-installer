<?php

namespace Wemx\Installer;

use Illuminate\Support\ServiceProvider;
use Wemx\Installer\Commands\WemXInstaller;

class CommandsServiceProvider extends ServiceProvider
{

    private $wemx_backup;
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->commands([
            WemXInstaller::class,
        ]);
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
