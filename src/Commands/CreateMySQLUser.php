<?php

namespace Billing\Commands\Commands;

use Illuminate\Console\Command;

class CreateMySQLUser extends Command
{

  protected $signature = 'phpmyadmin:user:make';
  protected $description = 'Create an user account for phpMyAdmin';

  public function handle()
  {
    $user = $this->ask('Username:');
    if ($this->confirm("Allow remote connect?")) {
      $allow_ip = '%';
    } else {
      $allow_ip = 'localhost';
    }
    $db = $this->choice(
      "Select Databse:",
      $this->getDatabases()
    );
    $this->create($user, $db, $allow_ip);
  }

  private function create($username, $db, $allow_ip)
  {
    if ($db == "All") {
      $db = '*';
    }
    exec('mysql -u root -e "select User,Host from mysql.user WHERE User=\'' . $username . '\' "', $check);
    if (!empty($check)) {
      if (!$this->confirm("You already have a user named {$username}, are you sure you want to delete it?")) {
        $this->warn('The user was not deleted');
        return;
      }
      $host = explode('	', $check['1'])['1'];
      exec('mysql -u root -e "DROP USER \'' . $username . '\'@\'' . $host . '\';" > /dev/null 2>&1 &');
    }

    $pass = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
    $q0 = 'CREATE USER \'' . $username . '\'@\'' . $allow_ip . '\' IDENTIFIED BY \'' . $pass . '\';';
    $q1 = 'GRANT ALL PRIVILEGES ON ' . $db . '.* TO \'' . $username . '\'@\'' . $allow_ip . '\' WITH GRANT OPTION;';
    $sql = $q0 . $q1;
    exec('mysql -u root -e "' . $sql . '"');
    $this->info('User has been created:');
    $this->info('Username: ' . $username);
    $this->info('Password: ' . $pass);
  }

  private function getDatabases()
  {
    exec("mysql -u root -e 'SHOW DATABASES'", $output);
    $output['0'] = 'All';
    return $output;
  }
}
