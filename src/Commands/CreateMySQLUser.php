<?php

namespace Billing\Commands\Commands;

use Illuminate\Console\Command;

class CreateMySQLUser extends Command
{

    protected $signature = 'phpmyadmin:user:make
                            {--db= : A database which the phpmyadmin user will have access to}';
    protected $description = 'Create an user account for phpMyAdmin';

    public function handle()
    {
        $db = $this->choice(
            "Select Databse:",
            $this->getDatabases()
        );
        $this->create($db);
    }

    private function create($db)
    {
        if ($db == "All") {
            $db = '*';
        }
        exec('mysql -u root -e "select User, Host from mysql.user WHERE User=\'phpmyadmin\' "', $check);
        if (!empty($check)) {
            if (!$this->confirm("You already have a user named phpmyadmin, are you sure you want to delete it?")) {
                $this->warn('The user was not deleted');
                return;
            }
            $host = explode('	', $check['1'])['1'];
            exec('mysql -u root -e "DROP USER \'phpmyadmin\'@\'' . $host . '\';" > /dev/null 2>&1 &');
        }

        $pass = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
        $q0 = 'CREATE USER \'phpmyadmin\'@\'%\' IDENTIFIED BY \'' . $pass . '\';';
        $q1 = 'GRANT ALL PRIVILEGES ON ' . $db . '.* TO \'phpmyadmin\'@\'%\' WITH GRANT OPTION;';
        $sql = $q0 . $q1;
        exec('mysql -u root -e "' . $sql . '"');
        $this->info('User has been created:');
        $this->info('Username: phpmyadmin');
        $this->info('Password: ' . $pass);
    }

    private function getDatabases()
    {
        exec("mysql -u root -e 'SHOW DATABASES'", $output);
        $output['0'] = 'All';
        return $output;
    }
}