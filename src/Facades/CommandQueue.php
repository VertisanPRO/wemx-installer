<?php

namespace Wemx\Installer\Facades;

use Illuminate\Support\Facades\Cache;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class CommandQueue
{
    protected mixed $commands = [];

    public function __construct()
    {
        $this->commands = Cache::get('queue_commands', []);
    }

    public function get(): array
    {
        return $this->commands;
    }

    public function add(string $command, array $args = []): void
    {
        $this->commands[] = [
            'command' => $command,
            'arguments' => $args
        ];
        $this->update();
        $this->log()->info('Add command: ' . $command . ' args:' . json_encode($args));
    }

    public function remove(int $key): void
    {
        unset($this->commands[$key]);
        $this->log()->info('Remove command: ' . $key);
        $this->commands = array_values($this->commands);
        $this->update();
    }

    protected function update(): void
    {
        Cache::put('queue_commands', $this->commands);
    }

    public function log(): Logger
    {
        $log = new Logger('');
        $log->pushHandler(new StreamHandler(storage_path('logs/queue.log')));
        return $log;
    }
}
