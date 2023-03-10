<?php

namespace Wemx\Installer\Commands;

use Illuminate\Console\Command;

class UninstallCommand extends Command
{

    protected $signature = 'billing:uninstall
                            {--continue= : If the person wants to continue the uninstallation
                            {--installer= : If the person wants to remove the installer';

    protected $description = 'Removes the module and reinstalls the Pterodactyl panel';

    public function handle()
    {
        $installer = 0;
        if (!$this->confirm('Are you sure you want to continue with the uninstallation?') ?? $this->option('continue') == false) {
            $this->warn('Billing Module was not uninstalled');

            return;
        }
        if ($this->confirm('Would you like to remove the installer also?') ?? $this->option('installer') == true) {
            $installer = 1;
        }
        $commands = [
            'rm -rf app/Models/Billing >/dev/null 2>&1 &',
            'rm -rf app/Models/Plugins >/dev/null 2>&1 &',
            'rm -rf app/Http/Controllers/Billing >/dev/null 2>&1 &',
            'rm -rf app/Http/Controllers/GMD >/dev/null 2>&1 &',
            'rm -rf app/Http/Controllers/Plugins >/dev/null 2>&1 &',
            'rm -rf app/Http/Middleware/GMD.php >/dev/null 2>&1 &',
            'rm -rf database/migrations/billing >/dev/null 2>&1 &',
            'rm -rf routes/custom >/dev/null 2>&1 &',
            'rm -rf routes/gmd.php >/dev/null 2>&1 &',
        ];

        exec('php artisan down');
        exec('curl -L https://github.com/pterodactyl/panel/releases/latest/download/panel.tar.gz | tar -xzv');
        exec('chmod -R 755 storage/* bootstrap/cache');
        exec('echo \"yes\" | composer install --no-dev --optimize-autoloader');
        exec('php artisan view:clear && php artisan config:clear');
        exec('php artisan migrate --seed --force');

        exec('chown -R www-data:www-data ' . base_path() . '/* > /dev/null 2>&1 &');
        exec('chown -R nginx:nginx ' . base_path() . '/* > /dev/null 2>&1 &');
        exec('chown -R apache:apache ' . base_path() . '/* > /dev/null 2>&1 &');
        foreach ($commands as $value) {
            exec($value);
        }
        exec('php artisan queue:restart');
        exec('php artisan up');
        if ($installer == 0) {
            exec('echo \"yes\" | composer require wemx/installer');
        }

        return $this->info('Uninstalled the Billing Module and installed a fresh new Pterodactyl Panel');
    }
}