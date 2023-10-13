<?php

namespace Wemx\Installer\Commands\Setup;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SetupCommand extends Command
{
    protected $signature = 'wemx:setup {domain?} {path?} {ssl?}';
    protected $description = 'Setup command';

    public function handle(): void
    {
        $this->info('Configuring WebServer');

        $domain = $this->argument('domain') ?? $this->askDomain();
        $path = $this->argument('path') ?? $this->askRootPath();
        $ssl = $this->argument('ssl') ?? $this->confirm('Would you like to configure SSL?', true);

        $serverChoice = $this->choice('Which web server would you like to configure?', ['Nginx', 'Apache'], 0);
        if ($serverChoice === 'Apache') {
            passthru("php artisan wemx:apache $domain $path $ssl");
        } else {
            passthru("php artisan wemx:nginx $domain $path $ssl");
        }

        $this->info('Configuring Wemx');
        passthru('php artisan wemx:install --type=dev');
        passthru('cp .env.example .env');
        passthru('composer install --optimize-autoloader');
        if ($this->confirm('Generate encryption key? (Only run this command if you are installing WemX for the first time)', true)) {
            passthru("php artisan key:generate --force");
        }

        $this->info('Configuring Environment');
        $url = $ssl ? 'https://' . rtrim($domain, '/') : 'http://' . rtrim($domain, '/');
        passthru("php artisan setup:environment --url={$url} -n");


        $this->info('Configuring Database');
        if ($this->confirm('Do you want to create a new database?', true)) {
            passthru("php artisan wemx:database");
        }

        passthru("php artisan module:enable");
        passthru("php artisan storage:link");
        passthru("php artisan migrate --force");

        $this->info('Configuring Crontab');
        $command = "* * * * * php {$path}/artisan schedule:run >> /dev/null 2>&1";
        $currentCronJobs = shell_exec('crontab -l');
        if (!str_contains($currentCronJobs, $command)) {
            shell_exec('(crontab -l; echo "' . $command . '") | crontab -');
        }

        $this->info('Configuring User');
        if ($this->confirm('Create an administrator user?', true)) {
            passthru("php artisan user:create");
        }

        $this->info('Configuring WebServer permission');
        passthru("php artisan wemx:chown");
    }

    private function askRootPath(): string
    {
        $rootPath = $this->askWithCompletion('Please enter the root path to your Laravel project or press Enter to accept the default path:', [], base_path('public'));
        while (!is_dir($rootPath)) {
            $this->error('Invalid path. Please try again.');
            $rootPath = $this->askWithCompletion('Please enter the root path to your Laravel project or press Enter to accept the default path:', [], base_path('public'));
        }
        return $rootPath;
    }

    private function askDomain(): string
    {
        $domain = $this->ask('Please enter your domain without http:// or https:// (e.g., example.com)');
        while (!preg_match('/^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,6}$/', $domain)) {
            $this->error('Invalid domain. Please try again.');
            $domain = $this->ask('Please enter your domain without http:// or https:// (e.g., example.com)');
        }
        return $domain;
    }

}
