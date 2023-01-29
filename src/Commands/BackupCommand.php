<?php

namespace Wemx\Installer\Commands;

use Illuminate\Console\Command;

class BackupCommand extends Command
{

    protected $signature = 'backup:
    {a=create : create/list/restore}
    {--type= : panel/db/all}';
    protected $description = 'Backup for Pterodactyl';

    public function handle()
    {
    }
}
