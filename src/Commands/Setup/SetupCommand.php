<?php

namespace Wemx\Installer\Commands\Setup;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Wemx\Installer\Facades\CommandQueue;
use Wemx\Installer\Traits\EnvironmentWriterTrait;

class SetupCommand extends Command
{
    use EnvironmentWriterTrait;

    protected $signature = 'wemx:setup {webserver?} {domain?} {path?} {ssl?}';
    protected $description = 'Setup command';

    /**
     * @throws ExceptionInterface
     * @throws \Exception
     */
    public function handle(): void
    {
        $queue = new CommandQueue();
        $this->warn('Configuring WebServer');

        $domain = $this->argument('domain') ?? $this->askDomain();
        $path = $this->argument('path') ?? $this->askRootPath();
        $ssl = $this->argument('ssl') ?? $this->confirm('Would you like to configure SSL?', true);
        $webserver = $this->argument('webserver') ?? null;
        $license_key = $this->ask('Enter your WemX license key');
        $name = $this->ask('Please enter the name of the administrator');
        $email = $this->ask('Please enter the email of the administrator');
        $password = $this->secret('Please enter the password of the administrator');

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

        shell_exec('curl -o ' . base_path('.env') . ' https://raw.githubusercontent.com/VertisanPRO/wemx-installer/wemxpro/src/.env.example');
        while (!file_exists(base_path('.env'))) {
            $this->info('Waiting for .env file to be created...');
            shell_exec('curl -o ' . base_path('.env') . ' https://raw.githubusercontent.com/VertisanPRO/wemx-installer/wemxpro/src/.env.example');
            sleep(3);
        }

        if ($this->confirm('Setup encryption key. (Only run this command if you are installing WemX for the first time)', true)) {
            $key = shell_exec('php artisan key:generate --show');
            $this->warn('Encryption key is used to encrypt data that is stored in your database. After generating it, store it somewhere safe. You can find it in .env file under APP_KEY');
            $this->warn($key);
            $this->writeToEnvironment(['APP_KEY' => trim($key)]);
        }

        $this->warn('Database Creation');
        if ($this->confirm('Do you want to create a new database?', true)) {
            $databaseCommand = $this->getApplication()->find('wemx:database');
            $databaseCommand->run(new ArrayInput([]), $this->output);
            $databaseSettings['DB'] = '-----------------';
            $databaseSettings = array_merge($databaseSettings, $databaseCommand->getDatabaseSettings());
        }




        $this->warn('Configuring Crontab');
        $command = "* * * * * php {$path}/artisan schedule:run >> /dev/null 2>&1";
        $currentCronJobs = shell_exec('crontab -l');
        if (!str_contains($currentCronJobs, $command)) {
            shell_exec('(crontab -l; echo "' . $command . '") | crontab -');
        }

        $this->warn('WemX Installation');
        $this->call('wemx:install', ['license_key' => $license_key, '--type' => 'dev'], $this->output);
        passthru('composer install --optimize-autoloader --ansi -n');
        passthru('composer update --ansi -n');

        try {
            $this->warn('Attempting to perform migrations');
            Config::set('database.connections.mysql.host', '127.0.0.1');
            Config::set('database.connections.mysql.database', $databaseSettings['Database']);
            Config::set('database.connections.mysql.username', $databaseSettings['Username']);
            Config::set('database.connections.mysql.password', $databaseSettings['Password']);
            $this->call('migrate', ['--force' => true], $this->output);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        try {
            $user = new \App\Models\User();
            $user->password = Hash::make($password);
            $user->email = $email;
            $user->username = $name;
            $user->save();
            $this->info('Administrator account created successfully.');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        try {
            $user = new \App\Models\Settings::put('encrypted::license_key', $license_key);
            $this->info('License save successfully.');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        shell_exec("php artisan config:clear && php artisan cache:clear && php artisan view:clear && php artisan route:clear");
        passthru("php artisan storage:link");


        $this->warn('Configuring WebServer permission');
        passthru('composer update --ansi -n');
        shell_exec("php artisan wemx:chown");

        $data = [
            'License' => $license_key,
            'Domain' => $domain,
            'Path' => $path,
            'SSL' => $ssl ? 'Enabled' : 'Disabled',
            'WebServer' => $webserver,
            'AppKey' => $key ?? '',
        ];

        $admin['Administrator'] = '-----------------';
        $admin['Name'] = $name;
        $admin['Email'] = $email;
        $admin['Pass'] = $password;

        $combinedData = array_merge($data, $databaseSettings ?? [], $admin);
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
