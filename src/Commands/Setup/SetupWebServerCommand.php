<?php

namespace Wemx\Installer\Commands\Setup;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SetupWebServerCommand extends Command
{
    protected $signature = 'wemx:webserver {domain?} {path?} {ssl?}';
    protected $description = 'WebServer setup command';

    public function handle(): void
    {
        $this->info('Configuring WebServer');

        $domain = $this->argument('domain') ?? $this->askDomain();
        $path = $this->argument('path') ?? $this->askRootPath();
        $ssl = $this->argument('ssl') ?? $this->confirm('Would you like to configure SSL?', true);

        if ($this->confirm('Would you like to configure Apache?', true)) {
            Artisan::call('wemx:apache', [
                'domain' => $domain,
                'path' => $path,
                'ssl' => $ssl
            ]);
        } else {
            Artisan::call('wemx:nginx', [
                'domain' => $domain,
                'path' => $path,
                'ssl' => $ssl
            ]);
        }
    }

    private function askRootPath(): string
    {
        $rootPath = $this->askWithCompletion('Please enter the root path to your Laravel project or press Enter to accept the default path:', [], base_path('public'));
        while (!is_dir($rootPath)) {
            $this->error('Invalid path. Please try again.');
            $rootPath = $this->askWithCompletion('Please enter the root path to your Laravel project or press Enter to accept the default path:', [], base_path('public'));
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

}
