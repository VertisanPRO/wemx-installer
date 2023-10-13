<?php

namespace Wemx\Installer\Facades;

use Illuminate\Support\Facades\Cache;

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
    }

    public function remove(int $key): void
    {
        unset($this->commands[$key]);
        $this->commands = array_values($this->commands);
        $this->update();
    }

    protected function update(): void
    {
        Cache::put('queue_commands', $this->commands);
    }
}
