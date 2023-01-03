<?php

namespace Billing\Commands\Commands;

use Illuminate\Console\Command;

class InstallphpMyAdmin extends Command
{

  protected $signature = 'phpmyadmin:install';
  protected $description = 'Install phpMyAdmin on your Pterodactyl Panel;';


    public function rmrfdir($path) {
        $files = glob($path . '/*');
        foreach ($files as $file) {
            is_dir($file) ? $this->rmrfdir($file) : unlink($file);
        }
        rmdir($path);

        return;
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
        exec('unzip -o phpMyAdmin-latest-all-languages.zip -d public/phpmyadmin -q');
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
