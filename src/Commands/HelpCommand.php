<?php

namespace Billing\Commands;

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
      php artisan billing:install installer - updating the command to automatically install the module (recommended to run before each installation/update of the module)
      php artisan billing:install stable {license key}(optional) - install stable version
      php artisan billing:install dev {license key}(optional) - install dev version(no recommend!!!)
      ';
    return $this->infoNewLine($help);
  }

  private function infoNewLine($text)
  {
    $this->newLine();
    $this->info($text);
    $this->newLine();
  }
}
