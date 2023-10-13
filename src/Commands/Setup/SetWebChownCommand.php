<?php

namespace Wemx\Installer\Commands\Setup;

use Illuminate\Console\Command;
use Illuminate\Contracts\Queue\ShouldQueue;

class SetWebChownCommand extends Command implements ShouldQueue
{
    protected $signature = 'wemx:chown';
    protected $description = 'Set the webserver user/group as owner for the project directories';

    public function handle(): void
    {
        // Get web server user and group
        $user = trim(shell_exec('ps aux | grep -E "[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx" | grep -v root | head -n1 | cut -d " " -f1'));
        $group = trim(shell_exec('id -gn ' . $user));

        if (empty($user) || empty($group)) {
            $this->error('Could not determine the web server user/group.');
            return;
        }

        // Set ownership
        shell_exec("chown -R $user:$group " . base_path());
        $this->info("Ownership has been changed to $user:$group for " . base_path());
    }
}
