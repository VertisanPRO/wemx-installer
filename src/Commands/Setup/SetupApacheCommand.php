<?php

namespace Wemx\Installer\Commands\Setup;

use Illuminate\Console\Command;

class SetupApacheCommand extends Command
{
    protected $signature = 'wemx:apache {domain?} {path?} {ssl?}';
    protected $description = 'Apache setup command';

    protected string $domain;
    protected string $rootPath = '/var/www/wemx/public';
    protected string $apacheConfig;
    protected bool $useSSL = false;

    public function handle(): void
    {
        $this->info('Configuring Apache');
        $this->domain = $this->argument('domain') ?? $this->askDomain();
        $this->rootPath = $this->argument('path') ?? $this->askRootPath();
        $this->useSSL = $this->argument('ssl') !== null ? filter_var($this->argument('ssl'), FILTER_VALIDATE_BOOLEAN) : $this->confirm('Would you like to configure SSL?', true);

        $this->apacheConfig = $this->useSSL ? $this->generateApacheSSLConfig() : $this->generateApacheConfig();
        if ($this->saveAndLinkApacheConfig()) {
            if ($this->useSSL) {
                $this->installSSL();
            }
            shell_exec("sudo systemctl restart apache2");
        }
    }

    private function askRootPath(): string
    {
        $defaultPath = $this->argument('path') ?? base_path('public');
        $rootPath = $this->askWithCompletion('Please enter the root path to your Laravel project or press Enter to accept the default path:', [], $defaultPath);
        while (!is_dir($rootPath)) {
            $this->error('Invalid path. Please try again.');
            $rootPath = $this->askWithCompletion('Please enter the root path to your Laravel project or press Enter to accept the default path:', [], $defaultPath);
        }
        return $rootPath;
    }
    private function askDomain(): string
    {
        $domain = $this->ask('Please enter your domain without http:// or https:// (e.g., example.com)');
        while (!preg_match('/^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,6}$/', $domain)) {
            $this->error('Invalid domain. Please try again.');
            $domain = $this->ask('Please enter your domain without http:// or https:// (e.g., example.com)');
        }
        return $domain;
    }

    private function installSSL(): void
    {
        while (!file_exists("/etc/apache2/sites-available/{$this->domain}.conf")) {
            $this->info("Waiting for /etc/apache2/sites-available/{$this->domain}.conf to be available...");
            sleep(5);
        }

        $this->info('Checking for Certbot and its Nginx plugin...');
        $needToInstall = shell_exec("dpkg -l | grep -E 'certbot|python3-certbot-nginx'") === null;
        if ($needToInstall) {
            $this->info('Installing Certbot and its Nginx plugin...');
            shell_exec("sudo apt-get install certbot python3-certbot-nginx -y");
        }
        $this->info('Obtaining an SSL certificate...');
        shell_exec("sudo certbot --apache --apache-ctl /etc/apache2/sites-available -d {$this->domain} --email wemx@wemx.com --non-interactive --agree-tos");
        $this->info('SSL certificate installed successfully.');
    }

    private function saveAndLinkApacheConfig(): bool
    {
        $configPath = "/etc/apache2/sites-available/wemx.conf";
        if (file_exists($configPath)) {
            $this->error("The configuration file {$configPath} already exists. Aborting.");
            return false;
        }
        file_put_contents($configPath, $this->apacheConfig);
        shell_exec("sudo ln -s $configPath /etc/apache2/sites-enabled/wemx}.conf");
        shell_exec("sudo a2enmod rewrite");
        if ($this->useSSL) {
            shell_exec("sudo a2enmod ssl");
        }
        $this->info('Apache configuration saved and linked successfully. Apache has been restarted.');
        return true;
    }

    private function generateApacheSSLConfig(): string
    {
        return <<<EOL
<VirtualHost *:80>
  ServerName {$this->domain}

  RewriteEngine On
  RewriteCond %{HTTPS} !=on
  RewriteRule ^/?(.*) https://%{SERVER_NAME}/$1 [R,L]
</VirtualHost>

<VirtualHost *:443>
  ServerName {$this->domain}
  DocumentRoot "{$this->rootPath}"

  AllowEncodedSlashes On

  php_value upload_max_filesize 100M
  php_value post_max_size 100M

  <Directory "{$this->rootPath}">
    Require all granted
    AllowOverride all
  </Directory>

  SSLEngine on
  SSLCertificateFile /etc/letsencrypt/live/{$this->domain}/fullchain.pem
  SSLCertificateKeyFile /etc/letsencrypt/live/{$this->domain}/privkey.pem
</VirtualHost>
EOL;
    }

    private function generateApacheConfig(): string
    {
        return <<<EOL
<VirtualHost *:80>
  ServerName {$this->domain}
  DocumentRoot "{$this->rootPath}"

  AllowEncodedSlashes On

  php_value upload_max_filesize 100M
  php_value post_max_size 100M

  <Directory "{$this->rootPath}">
    AllowOverride all
    Require all granted
  </Directory>
</VirtualHost>
EOL;
    }

}
