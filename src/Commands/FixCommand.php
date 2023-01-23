<?php

namespace Wemx\Installer\Commands;

use Illuminate\Console\Command;

class FixCommand extends Command
{

    protected $signature = 'billing:fix';
    protected $description = 'Fix installer the Billing Module for Pterodactyl';

    public function handle()
    {
        $this->removeFile('app/Console/Commands/BillingModule.php');
        $this->removeFile('routes/custom/register_module.php');
        $this->removeFile('app/Http/Controllers/Billing/PayPalController.php');
        $this->removeFile('app/Models/Billing/PayPal.php');
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
