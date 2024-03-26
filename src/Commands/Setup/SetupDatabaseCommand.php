<?php

namespace Wemx\Installer\Commands\Setup;

use Illuminate\Console\Command;

class SetupDatabaseCommand extends Command
{
    protected $signature = 'wemx:database {username?} {password?} {database?}';
    protected $description = 'Database setup command';

    protected array $databaseSettings = [];
    protected ?string $username = null;
    protected ?string $password = null;
    protected ?string $database = null;

    public function handle()
    {
        $this->info('Configuring Database');

        $this->username = $this->argument('username') ?? null;
        $this->password = $this->argument('password') ?? null;
        $this->database = $this->argument('database') ?? null;

        if (is_null($this->username) || is_null($this->password) || is_null($this->database)) {
            $this->getUserInput();
        }
        $this->runCommands();
        $this->databaseSettings = [
            'Username' => $this->username,
            'Password' => $this->password,
            'Database' => $this->database,
        ];
    }

    public function getDatabaseSettings(): array
    {
        return $this->databaseSettings;
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
            "/usr/bin/mariadb -u root -e \"CREATE USER '{$this->username}'@'127.0.0.1' IDENTIFIED BY '{$this->password}';\"",
            "/usr/bin/mariadb -u root -e \"CREATE DATABASE {$this->database};\"",
            "/usr/bin/mariadb -u root -e \"GRANT ALL PRIVILEGES ON {$this->database}.* TO '{$this->username}'@'127.0.0.1' WITH GRANT OPTION;\"",
        ];
        foreach ($commands as $command) {
            shell_exec($command);
        }
        $this->info('Database configuration is complete');
    }

}
