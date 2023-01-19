<?php

namespace Wemx\Installer\Commands;

use Illuminate\Console\Command;
use Wemx\Installer\Commands\CreateMySQLUser;

class InstallphpMyAdmin extends Command
{

    protected $signature = 'phpmyadmin:install
                            {--os= : OS Version you have installed}
                            {--webserver= : Webserver you have installed}';
    protected $description = 'Install phpMyAdmin on your Pterodactyl Panel;';

    public const OS_VERSIONS = [
        'debian' => 'Debian (and its forks)',
        'centos' => 'CentOS (and its forks)',
    ];

    public const WEBSERVER = [
        'nginx' => 'Nginx',
        'apache' => 'Apache',
        'other' => 'Other'
    ];

    public function handle()
    {
        $this->variables['OS_VERSIONS'] = $this->option('os') ?? $this->choice(
            'What OS Version do you have?',
            self::OS_VERSIONS
        );
        $this->variables['WEBSERVER'] = $this->option('webserver') ?? $this->choice(
            'What Webserver are you running?',
            self::OS_VERSIONS
        );
        switch ($this->variables['OS_VERSIONS']) {
            case 'debian':
                $path_nginx = '/etc/nginx/sites-available/pterodactyl.conf';
                $path_apache = '/etc/apache/sites-available/pterodactyl.conf';
                $restart_nginx = 'systemctl restart nginx';
                $restart_apache = 'systemctl restart apache2';
                break;
            case 'centos':
                $path_nginx = '/etc/nginx/conf.d/pterodactyl.conf';
                $path_apache = '/etc/httpd/conf.d/pterodactyl.conf';
                $restart_nginx = 'systemctl restart nginx';
                $restart_apache = 'systemctl restart httpd';
                break;
        }
        chdir('..');
        if (!file_exists('phpmyadmin')) {
            mkdir('phpmyadmin');
            chdir('phpmyadmin');
            $data = file_get_contents('https://www.phpmyadmin.net/downloads/phpMyAdmin-latest-all-languages.zip');
            @file_put_contents('phpMyAdmin.zip', $data);
            exec('unzip -q -o phpMyAdmin.zip -d .');
            unlink('phpMyAdmin.zip');
            exec('mv phpMyAdmin-*/* .');
            $this->rmrfdir('phpMyAdmin-*');
            $this->info('Files installed, configuring webserver');
            if ($this->variables['OS_VERSIONS'] == 'nginx' && file_exists($path_nginx) == true) {
                $file_contents = file_get_contents($path_nginx);
                $lines = explode("\n", $file_contents);
                array_pop($lines);
                array_push($lines, "");
                array_push($lines, "    location /path {");
                array_push($lines, "        root /var/www/phpmyadmin;");
                array_push($lines, "        index index.php;");
                array_push($lines, "    }");
                array_push($lines, "}");
                $file_contents = implode("\n", $lines);
                file_put_contents($path_nginx, $file_contents);
                exec($restart_nginx);
                $this->info('phpMyAdmin has been successfully installed. It is available on ' . env('APP_URL') . '/phpmyadmin');
            } elseif ($this->variables['OS_VERSIONS'] == 'apache' && file_exists($path_nginx) == true) {
                $file_contents = file_get_contents($path_apache);
                $lines = explode("\n", $file_contents);
                array_pop($lines);
                array_push($lines, "");
                array_push($lines, "    <Directory \"/var/www/phpmyadmin\">");
                array_push($lines, "        Require all granted");
                array_push($lines, "        AllowOverride all");
                array_push($lines, "    </Directory>");
                array_push($lines, "</VirtualHost>");
                $file_contents = implode("\n", $lines);
                file_put_contents($path_apache, $file_contents);
                exec($restart_apache);
                $this->info('phpMyAdmin has been successfully installed. It is available on ' . env('APP_URL') . '/phpmyadmin');
            } elseif ($this->variables['OS_VERSIONS'] == 'nginx' && file_exists($path_nginx) == false || $this->variables['OS_VERSIONS'] == 'apache' && file_exists($path_nginx) == false) {
                $this->warn('You installed the Webserver config into a different file. You have to configure it yourself');
                $this->warn('Otherwise you do not have the selected Webserver installed');
                $this->info('phpMyAdmin has been partially installed');
            } else {
                $this->warn('You are not running a NGINX/Apache webserver. You have to configure it yourself');
                $this->info('phpMyAdmin has been partially installed');
            }
        } else {
            if (!$this->confirm('You already have a phpMyAdmin folder, are you sure you want to remove it?')) {
                $this->warn('Installation has been cancelled');

                return;
            }
            $this->rmrfdir('phpmyadmin');
            self::handle;
        }
        if (!$this->confirm('Would you like to create a MySQL account that will be available for phpMyAdmin?')) {
            $this->warn('User was not created');

            return;
        }
        return CreateMySQLUser::handle;
    }

    public function rmrfdir($dir)
    {
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

            if (!$this->rmrfdir($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }

        }

        return rmdir($dir);
    }
}