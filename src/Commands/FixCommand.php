<?php

namespace Wemx\Installer\Commands;

use Illuminate\Console\Command;

class FixCommand extends Command
{

    protected $signature = 'billing:fix';
    protected $description = 'Fix installer the Billing Module for Pterodactyl';

    public function handle()
    {
        $this->removeFile(base_path() . '/app/Console/Commands/BillingModule.php');
        $this->removeFile(base_path() . '/routes/custom/register_module.php');
    }

    private function removeFile($file_path)
    {
        if (file_exists($file_path)) {
            unlink($file_path);
            return true;
        }
        return false;
    }
}