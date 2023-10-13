<?php

namespace Wemx\Installer\Commands\Setup;

use Illuminate\Console\Command;

class SetupDatabaseCommand extends Command
{
    protected $signature = 'wemx:database {username?} {password?} {database?}';
    protected $description = 'Database setup command';

    protected string $username;
    protected string $password;
    protected string $database;

    public function handle(): void
    {
        $this->info('Configuring Database');

        $this->username = $this->argument('username');
        $this->password = $this->argument('password');
        $this->database = $this->argument('database');

        if (is_null($this->username) || is_null($this->password) || is_null($this->database)) {
            $this->getUserInput();
            $confirmation = $this->confirm("You entered: Username: {$this->username}, Password: {$this->password}, Database: {$this->database}. Is this correct?", true);
            while (!$confirmation) {
                $this->getUserInput();
                $confirmation = $this->confirm("You entered: Username: {$this->username}, Password: {$this->password}, Database: {$this->database}. Is this correct?", true);
            }
        }
        $this->runCommands();
    }

    private function getUserInput(): void
    {
        $this->username = $this->askWithCompletion('Please enter the database username', 'wemx');
        $this->password = $this->secret('Please enter the database password');
        $this->database = $this->askWithCompletion('Please enter the database name', 'wemx');
    }

    private function runCommands(): void
    {
        $commands = [
            "mysql -u root -p -e \"CREATE USER '{$this->username}'@'127.0.0.1' IDENTIFIED BY '{$this->password}';\"",
            "mysql -u root -p -e \"CREATE DATABASE {$this->database};\"",
            "mysql -u root -p -e \"GRANT ALL PRIVILEGES ON {$this->database}.* TO '{$this->username}'@'127.0.0.1' WITH GRANT OPTION;\"",
        ];
        foreach ($commands as $command) {
            shell_exec($command);
        }
        $this->info('Database configuration is complete');
    }

}
