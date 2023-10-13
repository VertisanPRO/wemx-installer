<?php

namespace Wemx\Installer\Commands;

use Exception;
use Illuminate\Console\Command;
use Wemx\Installer\Facades\CommandQueue;

class QueueCommands extends Command
{
    protected $description = 'Execute queuened commands from CommandQueue';

    protected $signature = 'queue:commands';

    /**
     * QueueCommands constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Handle command request to create a new user.
     *
     * @throws Exception
     */
    public function handle(CommandQueue $queue)
    {
        foreach($queue->get() as $key => $command) {
            $this->info("Running {$command}");
            $queue->remove($key);
        }

        $this->info("Queued commands completed");
    }
}
