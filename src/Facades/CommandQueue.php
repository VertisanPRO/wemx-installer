<?php

namespace Wemx\Installer\Facades;

use Illuminate\Support\Facades\Cache;

class CommandQueue
{
    protected $commands = [];

    public function __construct()
    {
        $this->commands = Cache::get('queue_commands', []);
    }

    /**
     * Get all queued commands
     * 
     * @return array
     */
    public function get(): array
    {
        return $this->commands;
    }

    /**
     * Add a command to the command queue
     * 
     * @return void
     */
    public function add(string $command): void
    {
        $this->commands[] = $command; // Directly add to the array
        $this->update();
    }

    /**
     * Remove a command from the command queue
     * 
     * @return void
     */
    public function remove(int $key): void
    {
        unset($this->commands[$key]);
        $this->commands = array_values($this->commands); // Re-index the array
        $this->update();
    }

    /**
     * Update commands in the cache
     * 
     * @return void
     */
    protected function update(): void
    {
        Cache::put('queue_commands', $this->commands);
    }
}
