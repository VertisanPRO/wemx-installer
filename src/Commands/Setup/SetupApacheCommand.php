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
                shell_exec("sudo a2enmod ssl");
            }
            shell_exec("sudo systemctl restart apache2");
            $this->info('Apache configuration is complete');
        }
    }

    private function askRootPath(): void
    {
        $defaultPath = $this->argument('path') ?? $this->rootPath;
        $this->rootPath = $this->askWithCompletion('Please enter the root path to your Laravel project or press Enter to accept the default path:', [], $defaultPath);
        while (!is_dir($this->rootPath)) {
            $this->error('Invalid path. Please try again.');
            $this->rootPath = $this->askWithCompletion('Please enter the root path to your Laravel project or press Enter to accept the default path:', [], $defaultPath);
        }
    }

    private function askDomain(): void
    {
        $this->newLine();
        $this->domain = $this->ask('Please enter your domain without http:// or https:// (e.g., example.com)');
        while (!preg_match('/^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,6}$/', $this->domain)) {
            $this->error('Invalid domain. Please try again.');
            $this->domain = $this->ask('Please enter your domain without http:// or https:// (e.g., example.com)');
        }
    }

    private function installSSL(): void
    {
        $this->info('Checking for Certbot and its Nginx plugin...');
        $needToInstall = shell_exec("dpkg -l | grep -E 'certbot|python3-certbot-nginx'") === null;
        if ($needToInstall) {
            $this->info('Installing Certbot and its Nginx plugin...');
            shell_exec("sudo apt-get install certbot python3-certbot-nginx -y");
        }
        $this->info('Obtaining an SSL certificate...');
        shell_exec("sudo certbot --nginx -d {$this->domain}");
        $this->info('SSL certificate installed successfully.');
    }

    private function saveAndLinkApacheConfig(): bool
    {
        $configPath = "/etc/apache2/sites-available/{$this->domain}.conf";
        if (file_exists($configPath)) {
            $this->error("The configuration file {$configPath} already exists. Aborting.");
            return false;
        }
        file_put_contents($configPath, $this->apacheConfig);
        shell_exec("sudo ln -s /etc/apache2/sites-available/{$this->domain}.conf /etc/apache2/sites-enabled/{$this->domain}.conf");
        shell_exec("sudo a2enmod rewrite");
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
