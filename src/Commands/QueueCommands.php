<?php

namespace Wemx\Installer\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Wemx\Installer\Facades\CommandQueue;

class QueueCommands extends Command
{
    protected $description = 'Execute queued commands from CommandQueue';
    protected $signature = 'queue:commands';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(CommandQueue $queue): void
    {
        foreach($queue->get() as $key => $commandData) {
            $command = $commandData['command'];
            $arguments = $commandData['arguments'];
            $this->info("Running {$command} with arguments: " . json_encode($arguments));
            $queue->log()->info("Running {$command} with arguments: " . json_encode($arguments));

            try {
                Artisan::call($command, $arguments);
                $output = Artisan::output();
                $queue->remove($key);
                $this->info("Successfully executed {$command}. Output: {$output}");
                $queue->log()->info("Successfully executed {$command}. Output: {$output}");
            } catch (Exception $e) {
                $this->error("Failed to execute {$command}: " . $e->getMessage());
                $queue->log()->error("Failed to execute {$command}: " . $e->getMessage());
            }
        }
        $this->info("Queued commands completed");
    }
}
