<?php

namespace Billing\Commands\Commands;

use Illuminate\Console\Command;

class InstallphpMyAdmin extends Command
{

  protected $signature = 'phpmyadmin:install';
  protected $description = 'Install phpMyAdmin on your Pterodactyl Panel;';

  public function handle()
  {
    $this->install();
  }

  private function install()
  {
    mkdir('public/phpmyadmin');
    exec('wget -O phpMyAdmin.zip https://www.phpmyadmin.net/downloads/phpMyAdmin-latest-all-languages.zip -q');
    exec('unzip -o phpMyAdmin.zip -d public/phpmyadmin -qq');
    unlink('phpMyAdmin.zip');
    exec('mv public/phpmyadmin/phpMyAdmin-*/* public/phpmyadmin');
    recurseRmdir('public/phpmyadmin/phpMyAdmin-*');
    return $this->info('phpMyAdmin has been successfully installed. It is available on yourdomain.com/phpmyadmin');
  }
}
