<?php

namespace Wemx\Installer\Commands\Setup;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SetupDatabaseCommand extends Command
{
    protected $signature = 'wemx:database {username?} {password?} {database?}';
    protected $description = 'Database setup command';

    protected ?string $username = null;
    protected ?string $password = null;
    protected ?string $database = null;

    public function handle(): void
    {
        $this->info('Configuring Database');

        $this->username = $this->argument('username') ?? null;
        $this->password = $this->argument('password') ?? null;
        $this->database = $this->argument('database') ?? null;

        if (is_null($this->username) || is_null($this->password) || is_null($this->database)) {
            $this->getUserInput();
            $confirmation = $this->confirm("You entered: Username: {$this->username}, Password: {$this->password}, Database: {$this->database}. Is this correct?", true);
            while (!$confirmation) {
                $this->getUserInput();
                $confirmation = $this->confirm("You entered: Username: {$this->username}, Password: {$this->password}, Database: {$this->database}. Is this correct?", true);
            }
        }
        $this->runCommands();
        if ($this->confirm('Save database settings in .env file?', true)) {
            if (!file_exists(base_path('.env'))) {
                copy(base_path('.env.example'), base_path('.env'));
                $this->info('.env file created successfully.');
            }
            shell_exec("php artisan config:clear && php artisan cache:clear && php artisan view:clear && php artisan route:clear");
            sleep(5);
            Artisan::call('setup:database', ['--database' => $this->database, '--username' => $this->username, '--password' => $this->password, '--host' => '127.0.0.1', '--port' => 3306 ], $this->output);
        }
    }

    private function getUserInput(): void
    {
        $this->username = $this->askWithCompletion('Please enter the database username', ['wemx']);
        $this->database = $this->askWithCompletion('Please enter the database name', ['wemx']);
        $this->password = $this->ask('Please enter the database password');
    }

    private function runCommands(): void
    {
        $commands = [
            "mysql -u root -e \"CREATE USER '{$this->username}'@'127.0.0.1' IDENTIFIED BY '{$this->password}';\"",
            "mysql -u root -e \"CREATE DATABASE {$this->database};\"",
            "mysql -u root -e \"GRANT ALL PRIVILEGES ON {$this->database}.* TO '{$this->username}'@'127.0.0.1' WITH GRANT OPTION;\"",
        ];
        foreach ($commands as $command) {
            shell_exec($command);
        }
        $this->info('Database configuration is complete');
    }
}
