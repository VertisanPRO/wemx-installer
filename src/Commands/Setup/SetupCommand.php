<?php

namespace Wemx\Installer\Commands\Setup;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Console\Input\ArrayInput;
use Wemx\Installer\Traits\EnvironmentWriterTrait;

class SetupCommand extends Command
{
    use EnvironmentWriterTrait;

    protected $signature = 'wemx:setup {webserver?} {domain?} {path?} {ssl?}';
    protected $description = 'Setup command';
    protected string $type = 'dev';

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        $this->warn('Configuring WebServer');





        $domain = $this->validateInput('ask', 'required|regex:/^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,6}$/', 'Invalid domain. Please try again.');
        $path = $this->argument('path') ?? $this->askRootPath();

        $ssl = $this->argument('ssl') ?? $this->confirm('Would you like to configure SSL?', true);
        $webserver = $this->argument('webserver') ?? null;
        $license_key = $this->ask('Enter your WemX license key');

        $name = $this->ask('Please enter the name of the administrator');
        $email = $this->validateInput('ask', 'required|email', 'Invalid email. Please try again.');
        $password = $this->validateInput('secret', 'required|min:6', 'Password must be at least 6 characters long. Please try again.');

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

        while (!file_exists(base_path('.env'))) {
            $this->info('Waiting for .env file to be created...');
            shell_exec('curl -o ' . base_path('.env') . ' https://raw.githubusercontent.com/VertisanPRO/wemx-installer/wemxpro/src/.env.example');
        }

        if ($this->confirm('Setup encryption key. (Only run this command if you are installing WemX for the first time)', true)) {
            $key = shell_exec('php artisan key:generate --show');
            $this->writeToEnvironment(['APP_KEY' => trim($key)]);
            Config::set('app.key', $key);
        }

        $this->warn('Database Creation');
        $database = $this->confirm('Do you want to create a new database?', true) ? $this->getDatabaseSettingsFromCommand() : $this->getDatabaseSettingsFromInput();
        $this->call('setup:database',
            ['--database' => $database['Database'], '--username' => $database['Username'], '--password' => $database['Password'], '--host' => '127.0.0.1', '--port' => 3306]);
        Config::set('database.connections.mysql.host', '127.0.0.1');
        Config::set('database.connections.mysql.database', $database['Database']);
        Config::set('database.connections.mysql.username', $database['Username']);
        Config::set('database.connections.mysql.password', $database['Password']);


        $this->warn('Configuring Crontab');
        $command = "* * * * * php {$path}/artisan schedule:run >> /dev/null 2>&1";
        $currentCronJobs = shell_exec('crontab -l');
        if (!str_contains($currentCronJobs, $command)) {
            shell_exec('(crontab -l; echo "' . $command . '") | crontab -');
        }

        $this->warn('WemX Installation');
        $this->call('wemx:install', ['license_key' => $license_key, '--type' => $this->type], $this->output);
        passthru('composer install --optimize-autoloader --ansi -n');
        passthru('composer update --ansi -n');

        try {
            $this->warn('Database migrations');
            $this->call('migrate', ['--force' => true], $this->output);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }

        try {
            $user = new \App\Models\User();
            $user->password = Hash::make($password);
            $user->email = $email;
            $user->username = $name;
            $user->save();
            $this->info('Administrator account created successfully.');
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }

        try {
            \DB::table('settings')->insert([
                'key' => 'encrypted::license_key',
                'value' => encrypt($license_key)
            ]);
            $this->info('License save successfully.');
        } catch (Exception $e) {
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

        $admin['Name'] = $name;
        $admin['Email'] = $email;
        $admin['Pass'] = $password;
        $this->displaySummaryTable($data, $database, $admin);
        $this->info('Configuring is complete, go to the url below to continue:');
    }

    private function displaySummaryTable(array $data, array $database, array $admin): void
    {
        $dataFormatted = [
            'License' => $data['License'],
            'Domain' => $data['Domain'],
            'Path' => $data['Path'],
            'SSL' => $data['SSL'],
            'WebServer' => $data['WebServer'],
            'AppKey' => $data['AppKey'] ?? '',
            ' ' => ' ',
            'Admin Account' => '-----------------',
            'Name' => $admin['Name'],
            'Email' => $admin['Email'],
            'Pass' => $admin['Pass'],
            ' ' => ' ',
            'Database Data' => '-----------------',
            'Database' => $database['Database'],
            'Username' => $database['Username'],
            'Password' => $database['Password'],
        ];

        $keys = array_keys($dataFormatted);
        $values = array_values($dataFormatted);
        $rows = array_map(null, $keys, $values);
        $this->table(['Key', 'Value'], $rows);
    }

    private function getDatabaseSettingsFromCommand(): array
    {
        $databaseCommand = $this->getApplication()->find('wemx:database');
        $databaseCommand->run(new ArrayInput([]), $this->output);
        return array_merge(['DB' => '-----------------'], $databaseCommand->getDatabaseSettings());
    }

    private function getDatabaseSettingsFromInput(): array
    {
        $this->warn('Enter the data of the existing database');
        return [
            'DB' => '-----------------',
            'Database' => $this->ask('Please enter the database username'),
            'Username' => $this->ask('Please enter the database name'),
            'Password' => $this->ask('Please enter the database password')
        ];
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

    private function validateInput(string $field, string $rule, string $errorMessage): string
    {
        do {
            $input = $this->$field('Please enter ' . strtolower($field));
            $validator = Validator::make([$field => $input], [$field => $rule]);
            if ($validator->fails()) {
                $this->error($errorMessage);
            }
        } while ($validator->fails());
        return $input;
    }
}
