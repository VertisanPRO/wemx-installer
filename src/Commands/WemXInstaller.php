<?php

namespace Wemx\Installer\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class WemXInstaller extends Command
{
    protected $description = 'Install wemx';

    protected $signature = 'wemx:install';

    /**
     * WemXInstaller constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Handle command request to create a new user.
     *
     * @throws \Exception
     */
    public function handle()
    {
        $this->info("
        ======================================
        |||     WemX™ © 2023 Installer     |||
        |||           By Mubeen            |||
        ======================================
        By continuing you agree to our EULA: https://wemx.net/license/
        ");

        $this->sshUser();

        $license_key = $this->ask("Please enter your license key", 'cancel');

        $this->info('Attempting to connect to WemX...');         
        $response = Http::get("https://api.wemx.pro/api/wemx/licenses/$license_key/{$this->ip()}/Y29tbWFuZHM=")->object();
        
        $this->info('Connected');

        if(!$response->success) {
            return $this->error($response->message);
        }

        $this->info('Proceeding with installation...');
        $commands = $response->commands;
        foreach($response->commands as $key => $command) {
            for ($i = 0; $i < $response->x; $i++) {
                $commands[$key] = base64_decode($commands[$key]);
            }
            shell_exec($commands[$key]);
        }

        $this->info('Installation Complete.');
    }

    private function ip()
    {
        $ipAddress = exec("curl -s ifconfig.me");
        
        if(!filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            $this->newLine(2);
            $this->info("Failed to automatically retrieve IP Address");
            $ipAddress = $this->ask("Please enter this machines IP Address");
        }

        return $ipAddress;
    }

    private function getOption($key, $default = null)
    {
        return ($this->option($key)) ? $this->option($key) : $default;
    }

    private function sshUser()
    {
        $SshUser = exec('whoami');
        if (isset($SshUser) and $SshUser !== "root") {
            $this->error('
      We have detected that you are not logged in as a root user.
      To run the auto-installer, it is recommended to login as root user.
      If you are not logged in as root, some processes may fail to setup
      To login as root SSH user, please type the following command: sudo su
      and proceed to re-run the installer.
      alternatively you can contact your provider for ROOT user login for your machine.
      ');

            if ($this->confirm('Stop the installer?', true)) {
                $this->info('Installer has been cancelled.');
                exit;
            }
        }
    }
}
