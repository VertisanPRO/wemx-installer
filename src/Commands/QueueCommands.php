<?php

namespace Wemx\Installer\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class QueueCommands extends Command
{
    protected $signature = 'wemx:queue 
                            {action : The action to perform (enqueue, process)} 
                            {--command= : The command to enqueue}
                            {--arguments= : The arguments for the command}';

    protected $description = 'Enqueue or Process queued commands';

    protected string $queueKey = 'command_queue';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $action = $this->argument('action');

        if ($action === 'enqueue') {
            $this->enqueueCommand();
        } elseif ($action === 'process') {
            $this->processQueue();
        } else {
            $this->error('Invalid action. Use enqueue or process.');
        }
    }

    protected function enqueueCommand(): void
    {
        $command = $this->option('command');
        $arguments = $this->option('arguments');
        $argumentsArray = !empty($arguments) ? explode(',', $arguments) : [];

        $queue = $this->getQueue();
        $queue[] = compact('command', 'argumentsArray');
        $this->saveQueue($queue);

        $this->log()->info("Command {$command} has been enqueued. Args {$arguments}");
        $this->info("Command {$command} has been enqueued.");
    }

    protected function processQueue(): void
    {
        while ($commandData = $this->dequeueCommand()) {
            $command = $commandData['command'];
            $arguments = $commandData['argumentsArray'];

            $this->info("Processing command: {$command} with arguments: " . implode(', ', $arguments));

            Artisan::call($command, $arguments);
            $this->log()->info("Run command: {$command}", ['args' => $arguments ?? [], 'output' => Artisan::output() ?? '']);
        }
        $this->info('Command queue processing completed.');
    }

    protected function getQueue(): array
    {
        return Cache::get($this->queueKey, []);
    }

    protected function saveQueue(array $queue): void
    {
        Cache::put($this->queueKey, $queue);
    }

    protected function dequeueCommand(): ?array
    {
        $queue = $this->getQueue();
        $commandData = array_shift($queue);
        $this->saveQueue($queue);
        return $commandData;
    }

    protected function log(): Logger
    {
        $log = new Logger('');
        $log->pushHandler(new StreamHandler(storage_path('logs/queue.log')));
        return $log;
    }
}
