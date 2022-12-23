<?php

namespace Billing\Commands\Commands;

use Illuminate\Console\Command;
use Pterodactyl\Models\Billing\Bill;

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
    $lic = Bill::settings();
    $lic->name = 'license_key';
    $lic->data = $this->argument('lic_key');
    $lic->save();
    return $this->info('License save to DB ' . $this->argument('lic_key'));
  }
}
