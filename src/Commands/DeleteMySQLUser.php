<?php

namespace Billing\Commands\Commands;

use Illuminate\Console\Command;

class DeleteMySQLUser extends Command
{

    protected $signature = 'phpmyadmin:user:delete';
    protected $description = 'Create an user account for phpMyAdmin';

    public function handle()
    {
        $this->delete();
    }

    private function delete()
    {
        if (!$this->confirm('Are you sure you want to delete the user?')) {
            $this->warn('The user was not deleted');

            return;
        }

        exec('mysql -u root -e "DROP USER \'phpmyadmin\'@\'%\';"');
        return $this->info('User has been deleted');
    }
}