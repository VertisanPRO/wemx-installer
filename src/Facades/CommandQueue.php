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
     * Get all quened commands
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
        array_push($this->commands, $command);
        $this->update($this->commands);
    }

    /**
     * Remove a command to the command queue
     * 
     * @return void
     */
    public function remove(int $key): void
    {
        unset($this->commands[$key]);
        $this->update($this->commands);
    }

    /**
     * Update commands in the array
     * 
     * @return void
     */
    protected function update(array $commands): void
    {
        Cache::put('queue_commands', $commands);
    }

}