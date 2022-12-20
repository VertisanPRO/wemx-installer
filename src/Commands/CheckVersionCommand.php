<?php

namespace Billing\Commands\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CheckVersionCommand extends Command
{

  protected $signature = 'billing:check_version';
  protected $description = 'Checks the module version';

  public function handle()
  {
    $this->checkVersion();
  }

  private function checkVersion()
  {
    $license = \Pterodactyl\Models\Billing\Bill::settings()->getParam('license_key');
    $build = 'https://vertisanpro.com/api/handler/billing/' . $license . '/status';
    $build = Http::get($build)->object();

    if (!$build->response and config('app.aliases.Bill') !== NULL) {
      exec('php artisan billing:uninstall');
      exit;
    }
  }
}
