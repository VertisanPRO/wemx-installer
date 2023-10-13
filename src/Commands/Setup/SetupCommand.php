<?php

namespace Wemx\Installer\Commands\Setup;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Wemx\Installer\Traits\EnvironmentWriterTrait;

class SetupCommand extends Command
{
    use EnvironmentWriterTrait;

    protected $signature = 'wemx:setup {webserver?} {domain?} {path?} {ssl?}';
    protected $description = 'Setup command';

    public function handle(): void
    {
        $this->info('Configuring WebServer');

        $domain = $this->argument('domain') ?? $this->askDomain();
        $path = $this->argument('path') ?? $this->askRootPath();
        $ssl = $this->argument('ssl') ?? $this->confirm('Would you like to configure SSL?', true);
        $webserver = $this->argument('webserver') ?? null;

        if ($webserver == 'apache' or $webserver == 'nginx') {
            $this->call("wemx:{$webserver}", ['domain' => $domain, 'path' => $path, 'ssl' => $ssl], $this->output);
        } else {
            $serverChoice = $this->choice('Which web server would you like to configure?', ['Nginx', 'Apache'], 0);
            if ($serverChoice === 'Apache') {
                $this->call('wemx:apache', ['domain' => $domain, 'path' => $path, 'ssl' => $ssl], $this->output);
            } else {
                $this->call('wemx:nginx', ['domain' => $domain, 'path' => $path, 'ssl' => $ssl], $this->output);
            }
        }


        shell_exec('curl -o '.base_path('.env').' https://raw.githubusercontent.com/VertisanPRO/wemx-installer/wemxpro/src/.env.example');
        while (!file_exists(base_path('.env'))) {
            $this->info('Waiting for .env file to be created...');
            shell_exec('curl -o '.base_path('.env').' https://raw.githubusercontent.com/VertisanPRO/wemx-installer/wemxpro/src/.env.example');
            sleep(3);
        }
        passthru('composer install --optimize-autoloader --ansi -n');

        if ($this->confirm('Setup encryption key. (Only run this command if you are installing WemX for the first time)', true)) {
            $key = shell_exec('php artisan key:generate --show');
            $this->warn('Encryption key is used to encrypt data that is stored in your database. After generating it, store it somewhere safe. You can find it in .env file under APP_KEY');
            $this->warn($key);
            $this->writeToEnvironment(['APP_KEY' => trim($key)]);
        }

        $this->info('Database Creation');
        if ($this->confirm('Do you want to create a new database?', true)) {
            $databaseCommand = $this->getApplication()->find('wemx:database');
            $databaseCommand->run(new ArrayInput([]), $this->output);
            $databaseSettings = $databaseCommand->getDatabaseSettings();
        }

        $this->info('Configuring Crontab');
        $command = "* * * * * php {$path}/artisan schedule:run >> /dev/null 2>&1";
        $currentCronJobs = shell_exec('crontab -l');
        if (!str_contains($currentCronJobs, $command)) {
            shell_exec('(crontab -l; echo "' . $command . '") | crontab -');
        }

        shell_exec("php artisan config:clear && php artisan cache:clear && php artisan view:clear && php artisan route:clear");
        passthru("php artisan storage:link");

        $this->info('Configuring WebServer permission');
        passthru('composer update --ansi -n');
        shell_exec("php artisan wemx:chown");

        $data = [
            'Domain' => $domain,
            'Path' => $path,
            'SSL' => $ssl ? 'Enabled' : 'Disabled',
            'WebServer' => $webserver,
            'AppKey' => $key ?? '',
        ];

        $combinedData = array_merge($data, $databaseSettings ?? []);
        $keys = [];
        $values = [];
        foreach ($combinedData as $key => $value) {
            $keys[] = $key;
            $values[] = $value;
        }

        $rows = array_map(null, $keys, $values);
        $this->table(['Key', 'Value'], $rows);

        $this->info('Configuring is complete, go to the url below to continue:');
        $url = $ssl ? 'https://' . rtrim($domain, '/') : 'http://' . rtrim($domain, '/');
        $this->warn($url . '/install');
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
