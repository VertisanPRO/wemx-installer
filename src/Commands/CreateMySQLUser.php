<?php

namespace Billing\Commands\Commands;

use Illuminate\Console\Command;

class CreateMySQLUser extends Command
{

    protected $signature = 'phpmyadmin:user:make';
    protected $description = 'Create an user account for phpMyAdmin';

    public function handle()
    {
        $this->create();
    }

    private function create()
    {
        $this->info('If an error appears, ignore it');
        exec('mysql -u root -e "CREATE USER \'phpmyadmin\'@\'%\' IDENTIFIED BY test;"', $output);
        if (str_contains($output, 'Operation CREATE USER failed')) {
            if (!$this->confirm('You already have a user named phpmyadmin, are you sure you want to delete it?')) {
                $this->warn('The user was not deleted');

                return;
            }
        }
        exec('mysql -u root -e "DROP USER \'phpmyadmin\'@\'%\'; > /dev/null 2>&1 &"');
        $pass = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
        $q0 = 'CREATE USER \'phpmyadmin\'@\'%\' IDENTIFIED BY \'' . $pass . '\';';
        $q1 = 'GRANT ALL PRIVILEGES ON *.* TO \'phpmyadmin\'@\'%\' WITH GRANT OPTION;';
        $sql = $q0 . $q1;
        exec('mysql -u root -e "' . $sql . '"');
        $this->info('User has been created:');
        $this->info('Username: phpmyadmin');
        return $this->info('Password: ' . $pass);
    }
}