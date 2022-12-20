<?php

namespace Billing\Commands\Commands;

use Illuminate\Console\Command;

class UninstallCommand extends Command
{

  protected $signature = 'billing:uninstall';
  protected $description = 'Removes the module and reinstalls the Pterodactyl panel';

  public function handle()
  {
    $this->uninstall();
  }

  private function uninstall()
  {
    $this->info('Updating Pterodactyl to the latest version');

    /**
     * Commences update proccess and 
     * executes commands below into terminal. 
     */

    exec('php artisan down');
    exec('cd ' . base_path());
    exec('curl -L https://github.com/pterodactyl/panel/releases/latest/download/panel.tar.gz | tar -xzv');
    exec('chmod -R 755 storage/* bootstrap/cache');
    exec('echo \"yes\" | composer install --no-dev --optimize-autoloader');
    exec('php artisan view:clear && php artisan config:clear');
    exec('php artisan migrate --seed --force');

    exec('chown -R www-data:www-data ' . base_path() . '/*');
    exec('chown -R nginx:nginx ' . base_path() . '/*');
    exec('chown -R apache:apache ' . base_path() . '/*');


    exec('php artisan queue:restart');
    exec('php artisan up');
    $this->info('Update Complete - Successfully Installed the latest version of Pterodactyl Panel!');
  }
}
