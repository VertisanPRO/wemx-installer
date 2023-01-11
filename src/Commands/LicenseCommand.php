<?php

namespace Wemx\Installer\Commands;

use Illuminate\Console\Command;

class LicenseCommand extends Command
{

    protected $signature = 'billing:license {lic_key}';
    protected $description = 'Save license key to DB Billing Module for Pterodactyl';

    public function handle()
    {
        $this->setLicense();
    }

    private function setLicense()
    {
        if (file_exists(base_path() . '/app/Models/Billing/Bill.php')) {
            \Pterodactyl\Models\Billing\Bill::settings()->updateOrCreate(
                ['name' => 'license_key'],
                ['data' => $this->argument('lic_key')]
            );
            return $this->info('License save to DB ' . $this->argument('lic_key'));
        }
        return $this->info('Billing Module is not installed. To use this command, install the module');
    }
}
