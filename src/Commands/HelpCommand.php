<?php

namespace Billing\Commands\Commands;

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
        Commands ({} means optional, [] means choose, "license_key" means you need to type your License Key):
        php artisan billing:help - This menu 
        php artisan billing:install stable {--license="license_key"} - Install stable version of Billing Module
        php artisan billing:install dev {--license="license_key"} - Install development version of Billing Module (not recommended)
        php artisan billing:uninstall - Removes the Billing Module and reinstalls the Pterodactyl Panel (servers, nodes and settings are saved)
        php artisan billing:license {--license="license_key"} - Saves license key to DB
        php artisan billing:fix - Fixes bugs with the old installer
        php artisan yarn:install {--node=[v14,v15,v16]} - Install Node.JS with Yarn and build assets
        php artisan phpmyadmin:install - Install phpMyAdmin alongside your Pterodactyl Panel
        php artisan phpmyadmin:user:make - Creates an user that you can log in with in phpMyAdmin
        php artisan phpmyadmin:user:delete - Deletes the user called phpmyadmin from MySQL';
        return $this->info($help);
    }
}