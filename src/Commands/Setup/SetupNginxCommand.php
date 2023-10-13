<?php

namespace Wemx\Installer\Commands\Setup;

use Illuminate\Console\Command;

class SetupNginxCommand extends Command
{
    protected $signature = 'wemx:nginx {domain?} {path?} {ssl?}';
    protected $description = 'Nginx setup command';

    protected string $domain;
    protected string $phpSocket = '/run/php/php8.1-fpm.sock';
    protected string $rootPath;
    protected string $nginxConfig;
    protected bool $useSSL = false;

    public function handle(): void
    {
        $this->info('Configuring Nginx');
        $this->domain = $this->argument('domain') ?? $this->askDomain();
        $this->rootPath = $this->argument('path') ?? $this->askRootPath();
        $this->useSSL = $this->argument('ssl') !== null ? filter_var($this->argument('ssl'), FILTER_VALIDATE_BOOLEAN) : $this->confirm('Would you like to configure SSL?', true);
        $this->checkPhpSocket();

        $this->nginxConfig = $this->useSSL ? $this->generateNginxSSLConfig() : $this->generateNginxConfig();
        if ($this->saveAndLinkNginxConfig()) {
            if ($this->useSSL) {
                $this->installSSL();
            }
            shell_exec("sudo systemctl restart nginx");
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

    private function checkPhpSocket(): void
    {
        if (!file_exists($this->phpSocket)) {
            $this->error('PHP socket not found.');
            if ($this->confirm('Would you like to install PHP-FPM to create the socket?')) {
                $this->installPhpFpm();
            }
        }
    }
    private function installPhpFpm(): void
    {
        $this->info('Installing PHP-FPM...');
        shell_exec('sudo apt-get install php8.1-fpm -y');
        $this->info('PHP-FPM installed successfully.');
    }
    private function installSSL(): void
    {
        while (!file_exists("/etc/nginx/sites-available/{$this->domain}.conf")) {
            $this->info("Waiting for /etc/nginx/sites-available/{$this->domain}.conf to be available...");
            sleep(5);
        }

        $this->info('Checking for Certbot and its Nginx plugin...');
        $needToInstall = shell_exec("dpkg -l | grep -E 'certbot|python3-certbot-nginx'") === null;
        if ($needToInstall) {
            $this->info('Installing Certbot and its Nginx plugin...');
            shell_exec("sudo apt-get install certbot python3-certbot-nginx -y");
        }
        $this->info('Obtaining an SSL certificate...');
        shell_exec("sudo certbot --nginx --nginx-ctl /etc/nginx/sites-available -d {$this->domain} --email wemx@wemx.com --non-interactive --agree-tos");
        $this->info('SSL certificate installed successfully.');
    }

    private function saveAndLinkNginxConfig(): bool
    {
        $configPath = "/etc/nginx/sites-available/{$this->domain}.conf";
        if (file_exists($configPath)) {
            $this->error("The configuration file {$configPath} already exists. Aborting.");
            return false;
        }
        file_put_contents($configPath, $this->nginxConfig);
        shell_exec("sudo ln -s {$configPath} /etc/nginx/sites-enabled/{$this->domain}.conf");
        $this->info('Nginx configuration saved and linked successfully. Nginx has been restarted.');
        return true;
    }
    private function generateNginxSSLConfig(): string
    {
        return <<<EOL
server {
    listen 80;
    server_name {$this->domain};
    server_tokens off;
    return 301 https://\$server_name\$request_uri;
}

server {
    listen 443 ssl http2;
    server_name {$this->domain};

    root {$this->rootPath};
    index index.php;

    access_log /var/log/nginx/wemx.app-access.log;
    error_log  /var/log/nginx/wemx.app-error.log error;

    client_max_body_size 100m;
    client_body_timeout 120s;

    sendfile off;

    ssl_certificate /etc/letsencrypt/live/{$this->domain}/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/{$this->domain}/privkey.pem;
    ssl_session_cache shared:SSL:10m;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers "ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305:DHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES256-GCM-SHA384";
    ssl_prefer_server_ciphers on;

    add_header X-Content-Type-Options nosniff;
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Robots-Tag none;
    add_header Content-Security-Policy "frame-ancestors 'self'";
    add_header X-Frame-Options DENY;
    add_header Referrer-Policy same-origin;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass unix:{$this->phpSocket};
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param PHP_VALUE "upload_max_filesize = 100M \\n post_max_size=100M";
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        fastcgi_param HTTP_PROXY "";
        fastcgi_intercept_errors off;
        fastcgi_buffer_size 16k;
        fastcgi_buffers 4 16k;
        fastcgi_connect_timeout 300;
        fastcgi_send_timeout 300;
        fastcgi_read_timeout 300;
        include /etc/nginx/fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
EOL;
    }
    private function generateNginxConfig(): string
    {
        return <<<EOL
server {
    listen 80;
    server_name {$this->domain};

    root {$this->rootPath};
    index index.html index.htm index.php;
    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    access_log off;
    error_log  /var/log/nginx/wemx.app-error.log error;

    client_max_body_size 100m;
    client_body_timeout 120s;

    sendfile off;

    location ~ \.php$ {
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass unix:{$this->phpSocket};
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param PHP_VALUE "upload_max_filesize = 100M \\n post_max_size=100M";
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        fastcgi_param HTTP_PROXY "";
        fastcgi_intercept_errors off;
        fastcgi_buffer_size 16k;
        fastcgi_buffers 4 16k;
        fastcgi_connect_timeout 300;
        fastcgi_send_timeout 300;
        fastcgi_read_timeout 300;
    }

    location ~ /\.ht {
        deny all;
    }
}
EOL;
    }

}
