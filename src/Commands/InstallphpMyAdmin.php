<?php

namespace Billing\Commands\Commands;

use Illuminate\Console\Command;

class InstallphpMyAdmin extends Command
{

  protected $signature = 'phpmyadmin:install';
  protected $description = 'Install phpMyAdmin on your Pterodactyl Panel;';


function rmrfdir($dir) {
    if (!file_exists($dir)) {
        return true;
    }

    if (!is_dir($dir)) {
        return unlink($dir);
    }

    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }

        if (!rmrfdir($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }

    }

    return rmdir($dir);
}

  public function handle()
  {
    $this->install();
  }

  private function install()
  {
    if (!file_exists('public/phpmyadmin')) {
        mkdir('public/phpmyadmin');
        if(!file_exists('phpMyAdmin-latest-all-languages.zip')) {
            exec('wget https://www.phpmyadmin.net/downloads/phpMyAdmin-latest-all-languages.zip -q');
        } else {
            unlink('phpMyAdmin-latest-all-languages.zip');
            exec('wget https://www.phpmyadmin.net/downloads/phpMyAdmin-latest-all-languages.zip -q');
        }
        exec('unzip -q -o phpMyAdmin-latest-all-languages.zip -d public/phpmyadmin');
        unlink('phpMyAdmin-latest-all-languages.zip');
        exec('mv public/phpmyadmin/phpMyAdmin-*/* public/phpmyadmin');
        $this->rmrfdir('public/phpmyadmin/phpMyAdmin-*');
        return $this->info('phpMyAdmin has been successfully installed. It is available on yourdomain.com/phpmyadmin');
    } else {
        if (!$this->confirm('You already have a phpMyAdmin folder, are you sure you want to remove it?')) {
            $this->warn('Installation has been cancelled');

            return;
        }
        $this->rmrfdir('public/phpmyadmin');
        exec('php artisan phpmyadmin:install');
    }
  }
}
