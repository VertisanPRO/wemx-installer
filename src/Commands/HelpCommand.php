<?php

namespace Billing\Commands\Commands;

use Illuminate\Console\Command;

class HelpCommand extends Command
{

  protected $signature = 'billing:help';
  protected $description = 'Help the Billing Module for Pterodactyl';

  public function handle()
  {
    $this->help();
  }

  private function help()
  {
    $help = '
  Help:
  php artisan billing:help - Help
  php artisan billing:install stable {license key}(optional) - install stable version
  php artisan billing:install dev {license key}(optional) - install dev version(no recommend!!!)
  php artisan billing:uninstall - Removes the module and reinstalls the Pterodactyl panel
  php artisan billing:fix - Fixes bugs with the old installer
  ';
    return $this->info($help);
  }
}
