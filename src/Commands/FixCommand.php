<?php

namespace Billing\Commands\Commands;

use Illuminate\Console\Command;

class FixCommand extends Command
{

  protected $signature = 'billing:fix';
  protected $description = 'Fix installer the Billing Module for Pterodactyl';

  public function handle()
  {
    $this->fix();
  }

  private function fix()
  {
    if (file_exists(base_path() . '/app/Console/Commands/BillingModule.php')) {
      unlink(base_path() . '/app/Console/Commands/BillingModule.php');
      return $this->info('Old installer removed');
    }
    return $this->info('No problems were found');
  }
}
