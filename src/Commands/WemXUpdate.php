<?php

namespace Wemx\Installer\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class WemXUpdate extends Command
{
    protected $description = 'Update WemX to a specified version';

    protected $signature = 'wemx:update {license_key?} {--type=stable} {--ver=latest}';

    /**
     * WemXUpdate constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Handle command request to create a new user.
     *
     * @throws Exception
     */
    public function handle()
    {
        $this->info("
        ======================================
        |||     WemX™ © 2023 Updater       |||
        |||           By Mubeen            |||
        ======================================
        ");

        $this->sshUser();

        $license_key = $this->argument('license_key') ?? $this->ask("Please enter your license key", settings('encrypted::license_key'));

        $this->info('Attempting to connect to WemX...');

        $queryParameters = [
                'type' => $this->getOption('type', 'stable'),
                'ver' => $this->getOption('ver', 'latest')
        ];

        $response = Http::withOptions([
            'query' => $queryParameters,
        ])->get("https://api.wemx.pro/api/wemx/licenses/$license_key/{$this->ip()}/Y29tbWFuZHM=");

        $this->info('Connected');

        if (!$response->successful()) {
            if (isset($response['success']) and !$response['success']) {
                $this->updateProgress(__('admin.update_failed'). $response['message'], 3);
                $this->error($response['message']);
                return;
            }
            
            $this->updateProgress(__('admin.failed_connect_remote_server_try_again'), 3);
            $this->error('Failed to connect to remote server, please try again.');
            return;
        }

        $response = $response->object();
        $this->info('Proceeding with installation of update...');
        $this->info('This can take a minute, please wait...');

        $this->updateProgress(__('admin.downloading_latest_assets'));

        $commands = $response->commands;
        foreach ($response->commands as $key => $command) {
            for ($i = 0; $i < $response->x; $i++) {
                $commands[$key] = base64_decode($commands[$key]);
            }
            shell_exec($commands[$key]);
        }

        $this->updateProgress(__('admin.unpacking_files_downloading_dependencies'));
        $this->info('Setting correct file permissions');
        shell_exec('chmod -R 755 storage/* bootstrap/cache');

        $this->info('Updating composer dependencies');
        shell_exec('composer update -n /dev/null 2>&1');
        shell_exec('composer install --optimize-autoloader -n /dev/null 2>&1');

        $this->updateProgress(__('admin.clearing_cache_optimizing_application'));
        $this->info('Enabling modules');
        shell_exec('php artisan module:enable');
        shell_exec('php artisan module:update');
        shell_exec('php artisan module:publish');

        $this->info('Clearing cache');
        shell_exec('php artisan view:clear && php artisan config:clear');

        $this->updateProgress(__('admin.migrating_seeding_database'));
        $this->info('Migrating & Seeding database');
        shell_exec('php artisan migrate --seed --force');

        $this->info('Updating webserver permissions');
        shell_exec('chown -R www-data:www-data '. base_path('/*'));

        // update the license key
        shell_exec('php artisan license:update '. $license_key);

        $this->updateProgress(__('admin.installed_successfully_please_refresh_page'), 3);
        $this->info('Update Complete');

        $this->info('Please update your license key using php artisan license:update');
    }

    private function ip()
    {
        $ipAddress = exec("curl -s ifconfig.me");

        if (!filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            $this->newLine(2);
            $this->info("Failed to automatically retrieve IP Address");
            $ipAddress = $this->ask("Please enter this machines IP Address");
        }

        return $ipAddress;
    }

    private function getOption($key, $default = null)
    {
        return ($this->option($key)) ? $this->option($key) : $default;
    }

    private function sshUser(): void
    {
        $SshUser = exec('whoami');
        if (isset($SshUser) and $SshUser !== "root") {
            $this->updateProgress(__('admin.updater_requires_root_user_permissions'), 3);
            $this->error('
      We have detected that you are not logged in as a root user.
      To run the auto-updater, it is recommended to login as root user.
      If you are not logged in as root, some processes may fail to setup
      To login as root SSH user, please type the following command: sudo su
      and proceed to re-run the installer.
      alternatively you can contact your provider for ROOT user login for your machine.
      ');

            if ($this->confirm('Stop the updater?', true)) {
                $this->info('updater has been cancelled.');
                exit;
            }
        }
    }

    protected function updateProgress(string $progress, int $lifetime = 120): void
    {
        $app_updating = Cache::get('app_updating');

        if ($app_updating) {
            $app_updating['progress'] = $progress;
            Cache::put('app_updating', $app_updating, $lifetime);
        }
    }
}
