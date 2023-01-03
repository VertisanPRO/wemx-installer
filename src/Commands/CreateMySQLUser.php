<?php

namespace Billing\Commands\Commands;

use Illuminate\Console\Command;

class CreateMySQLUser extends Command
{

  protected $signature = 'phpmyadmin:user';
  protected $description = 'Create an user account for phpMyAdmin';

  public function handle()
  {
    $this->install();
  }

  private function install()
  {
    $pass = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
    $q0 = 'CREATE USER \'phpmyadmin\'@\'%\' IDENTIFIED BY \'' . $pass . '\';';
    $q1 = 'GRANT ALL PRIVILEGES ON ' . config('database.connections.mysql.database', 'panel') . '.* TO \'phpmyadmin\'@\'%\' WITH GRANT OPTION;';
    $sql = $q0 . $q1;
    exec('mysql -u root -e ' . $sql);
    $this->info('User has been created:');
    $this->info('Username: phpmyadmin');
    return $this->info('Password:' . $pass);
  }
}
