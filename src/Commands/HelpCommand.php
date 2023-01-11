<?php

namespace Wemx\Installer\Commands;

use Illuminate\Console\Command;

class HelpCommand extends Command
{

    protected $signature = 'billing:help';
    protected $description = 'Help the Billing Module for Pterodactyl';

    public function handle()
    {
        $this->help();
    }

    private function help()
    {
        $help = '
            Commands ({} means optional, [] means choose, "" means you need to type what is written in quotation marks:
            php artisan billing:help - This menu 
            php artisan billing:install stable {"license_key"} {"ver_num"} - Install stable version of Billing Module
            php artisan billing:install dev {"license_key"} {"ver_num"} - Install development version of Billing Module (not recommended)
            php artisan billing:uninstall - Removes the Billing Module and reinstalls the Pterodactyl Panel (servers, nodes and settings are saved)
            php artisan billing:fix - Removes the old installer to not encounter bugs
            php artisan yarn:install {--node=[v14,v15,v16]} - Install Node.JS with Yarn and build assets
            php artisan phpmyadmin:install - Install phpMyAdmin alongside your Pterodactyl Panel
            php artisan phpmyadmin:user:make {--db="database_name"} - Creates an user that you can log in into phpMyAdmin
            php artisan phpmyadmin:user:delete - Deletes the user called phpmyadmin from MySQL';
        return $this->info($help);
    }
}