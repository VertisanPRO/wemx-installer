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

    protected $signature = 'wemx:setup {webserver?} {domain?} {path?} {ssl?} {--type= : The type of setup (dev or stable)}';
    protected $description = 'Setup command';

    protected string $domain;
    protected string $path;
    protected bool $ssl = false;
    protected string $type = 'stable';
    protected string $webserver;
    protected string $license_key;
    protected string $app_key = '';

    protected string $username;
    protected string $email;
    protected string $password;
    protected array $database;

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        $this->domain = $this->validateInput('ask',
            'required|regex:/^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,6}$/',
            'Please enter your domain without http:// or https:// (e.g., example.com)',
            'Invalid domain. Please try again.'
        );
        $this->path = $this->argument('path') ?? $this->askRootPath();
        $this->ssl = $this->argument('ssl') ?? $this->confirm('Would you like to configure SSL?', true);
        $this->type = $this->option('type') ?? 'stable';
        $this->webserver = $this->argument('webserver') ?? '';
        $this->license_key = $this->ask('Enter your WemX license key');

        $this->username = $this->ask('Please enter the name of the administrator');
        $this->email = $this->validateInput('ask',
            'required|email',
            'Please enter the email of the administrator',
            'Invalid email. Please try again.'
        );
        $this->password = $this->validateInput('secret',
            'required|min:6',
            'Please enter the password of the administrator',
            'Password must be at least 6 characters long. Please try again.'
        );

        $this->warn('WemX Installation');
        $this->call('wemx:install', ['license_key' => $this->license_key, '--type' => $this->type], $this->output);
        passthru('composer install --optimize-autoloader --ansi -n');

        while (!file_exists(base_path('.env'))) {
            $this->info('Waiting for .env file to be created...');
            shell_exec('cp .env.example .env');
        }

        if ($this->confirm('Setup encryption key. (Only run this command if you are installing WemX for the first time)', true)) {
            $this->app_key = shell_exec('php artisan key:generate --show');
            Config::set('app.key', $this->app_key);
        }

        $this->setupWebServer();
        $this->setupDatabase();
        $this->setupEnv();


        $this->warn('Configuring Crontab');
        $command = "* * * * * php " . base_path() . "/artisan schedule:run >> /dev/null 2>&1";
        $currentCronJobs = shell_exec('crontab -l');
        if (!str_contains($currentCronJobs, $command)) {
            shell_exec('(crontab -l; echo "' . $command . '") | crontab -');
        }

        $this->warn('Database migrations');
        $this->call('migrate', ['--force' => true], $this->output);

        $this->createUser();
        $this->saveLicense();

        passthru("php artisan storage:link");
        $this->warn('Configuring WebServer permission');
        shell_exec("php artisan wemx:chown");


        $this->displaySummaryTable();
        shell_exec("php artisan config:clear && php artisan cache:clear && php artisan view:clear && php artisan route:clear");
        $this->info('Configuring is complete, go to the url below to continue:');
    }

    private function displaySummaryTable(): void
    {
        $dataFormatted = [
            'License' => $this->license_key,
            'Domain' => $this->ssl ? 'https://' . $this->domain : 'http://' . $this->domain,
            'Path' => $this->path,
            'SSL' => $this->ssl,
            'WebServer' => ucfirst($this->webserver),
            'AppKey' => trim($this->app_key),

            'Admin Account' => '----------------------------------------------------------------------',
            'Name' => $this->username,
            'Email' => $this->email,
            'Pass' => $this->password,

            'Database Data' => '----------------------------------------------------------------------',
            'Database' => $this->database['Database'],
            'Username' => $this->database['Username'],
            'Password' => $this->database['Password'],
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

    private function setupWebServer(): void
    {
        if ($this->webserver == 'apache' or $this->webserver == 'nginx') {
            $this->call("wemx:{$this->webserver}", ['domain' => $this->domain, 'path' => $this->path, 'ssl' => $this->ssl], $this->output);
        } else {
            $serverChoice = $this->choice('Which web server would you like to configure?', ['Nginx', 'Apache'], 0);
            if ($serverChoice === 'Apache') {
                $this->call('wemx:apache', ['domain' => $this->domain, 'path' => $this->path, 'ssl' => $this->ssl], $this->output);
            } else {
                $this->call('wemx:nginx', ['domain' => $this->domain, 'path' => $this->path, 'ssl' => $this->ssl], $this->output);
            }
        }
    }

    private function setupDatabase(): void
    {
        $this->warn('Database Creation');
        $databaseOption = $this->choice('Do you want to create a new database or use an existing one?', ['Create New', 'Use Existing'], 0);
        $this->database = $databaseOption === 'Create New' ? $this->getDatabaseSettingsFromCommand() : $this->getDatabaseSettingsFromInput();
        $this->call('setup:database', [
            '--database' => $this->database['Database'],
            '--username' => $this->database['Username'],
            '--password' => $this->database['Password'],
            '--host' => '127.0.0.1', '--port' => 3306]
        );
        Config::set('database.connections.mysql.host', '127.0.0.1');
        Config::set('database.connections.mysql.database', $this->database['Database']);
        Config::set('database.connections.mysql.username', $this->database['Username']);
        Config::set('database.connections.mysql.password', $this->database['Password']);
    }

    private function createUser(): void
    {
        try {
            $user = new \App\Models\User();
            $user->password = Hash::make($this->password);
            $user->email = $this->email;
            $user->username = $this->username;
            $user->save();
            $this->info('Administrator account created successfully.');
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    private function saveLicense(): void
    {
        try {
            \DB::table('settings')->insert([
                'key' => 'encrypted::license_key',
                'value' => encrypt($this->license_key)
            ]);
            $this->info('License save successfully.');
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    private function setupEnv(): void
    {
        $this->writeToEnvironment([
            'APP_NAME' => 'WemX',
            'APP_ENV' => 'production',
            'APP_KEY' => trim($this->app_key),
            'APP_URL' => $this->ssl ? 'https://' . $this->domain : 'http://' . $this->domain,
            'APP_DEBUG' => $this->type == 'dev',
            'LARAVEL_CLOUDFLARE_ENABLED' => false
        ]);
    }

    private function validateInput(string $type, string $rule, string $ask, string $errorMessage): string
    {
        do {
            $input = $this->$type($ask);
            $validator = Validator::make([$type => $input], [$type => $rule]);
            if ($validator->fails()) {
                $this->error($errorMessage);
            }
        } while ($validator->fails());
        return $input;
    }
}
