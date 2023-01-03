<?php

namespace Billing\Commands\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class InstallCommand extends Command
{

  protected $signature = 'billing:install {ver=stable} {lic_key?} {ver_num=latest}';
  protected $description = 'Installs the Billing Module for Pterodactyl';
  private $install = [];

  private $url, $data;

  public function __construct()
  {
    parent::__construct();
    $this->url = 'https://api.vertisanpro.com/billing';
    $this->data = [];
  }

  public function handle()
  {
    $this->prepareArgs($this->arguments());
    $this->install();
  }

  private function install()
  {
    $this->infoNewLine("
       ======================================
       |||  Billing Module Installer      |||
       |||          By Gigabait & Mubeen  |||
       ======================================");

    $this->sshUser();
    if (!isset($this->install['lic_key'])) {
      $lic_key = $this->ask("Please enter a license key.");
      $this->install['lic_key'] = $lic_key;
    }

    $this->infoNewLine("Your license key is: {$this->install['lic_key']}");

    $this->data = [
      'license_key' => $this->install['lic_key'],
      'ver_type' => $this->install['ver'],
      'ver_num' => $this->install['ver_num'],
    ];

    $response = Http::get($this->url, $this->data);
    $this->dataPrepare($response->object());

    if (isset($this->getData()['end_message'])) {
      $this->infoNewLine($this->getData()['end_message']);
    }
  }

  private function setData($key, $value)
  {
    $this->data[$key] = $value;
  }

  private function getData()
  {
    return $this->data;
  }

  private function funcPrepare($data)
  {
    if (isset($data->text)) {
      $this->infoNewLine($data->text);
      unset($data->text);
    }
    if (is_array($data->func)) {
      foreach ($data->func as $value) {
        call_user_func($value);
      }
      return;
    }
    if ($data->resp) {
      if (isset($data->args)) {
        $this->setData($data->key, call_user_func($data->func, $data->args));
      } else {
        $this->setData($data->key, call_user_func($data->func));
      }
    } else {
      if (isset($data->args)) {
        call_user_func($data->func, $data->args);
      } else {
        call_user_func($data->func);
      }
    }
  }

  private function cmdPrepare($data)
  {
    if (isset($data->text)) {
      $this->infoNewLine($data->text);
      unset($data->text);
    }

    if (is_array($data->cmd)) {
      foreach ($data->cmd as $value) {
        exec($value);
      }
      return;
    }

    if ($data->resp) {
      $this->setData($data->key, exec($data->cmd));
    } else {
      exec($data->cmd);
    }
  }

  private function dataPrepare($data)
  {
    if (!$data->status) {
      $this->warn($data->text);
      unset($data->text);
      return;
    }
    if (isset($data->text)) {
      $this->infoNewLine($data->text);
      unset($data->text);
    }
    foreach ($data as $key => $value) {
      if (isset($value->func)) {
        $this->funcPrepare($value);
        continue;
      }
      if (isset($value->cmd)) {
        $this->cmdPrepare($value);
        continue;
      }
      $this->setData($key, $value);
    }

    if ($this->getData()['url']) {
      $response = Http::get($this->url, $this->getData());
      $this->dataPrepare($response->object());
    }
  }

  private function prepareArgs($arguments)
  {
    foreach ($arguments as $key => $val) {
      $this->install[$key] = $val;
    }
    unset($this->install['command']);
  }

  private function infoNewLine($text)
  {
    $this->newLine();
    $this->info($text);
    $this->newLine();
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

  private function installSocialite()
  {

    if (version_compare(config('app.version'), '1.9.2') < 0) {
      $this->error('Could not install Socialite SSO Logins, Socalite requires Pterodactyl 1.9.2 or above, you have [' . config('app.version') . '].');
      return 0;
    }

    $this->info('Downloading... Socialite with Composer');
    exec("echo \"yes\" | composer require laravel/socialite");

    $this->info('Downloading... Discord Driver');
    exec("echo \"yes\" | composer require socialiteproviders/discord");

    $this->info("Clearing Laravel Cache");
    exec('php artisan view:clear && php artisan config:clear');

    if (!config()->has('services.socialite')) {
      $this->info("Cleaning up challenges");
      exec('cp config/services.php config/services-backup.php');
      exec('cp config/services-sl.php config/services.php');
    }
  }

  private function setupCron()
  {
    $schedular = exec("crontab -l | grep -q '/billing/scheduler'  && echo 'true' || echo 'false'");
    $version = exec("crontab -l | grep -q 'check_version'  && echo 'true' || echo 'false'");
    if (isset($schedular) and $schedular == 'false') {
      $this->infoNewLine("Setup scheduler Cron");
      exec('(crontab -l ; echo "0 0 * * * curl ' . config('app.url') . '/billing/scheduler") | sort - | uniq - | crontab -');
    }

    if (isset($version) and $version == 'false') {
      exec('(crontab -l ; echo "0 6 * * * cd ' . base_path() . ' && php artisan billing:check_version") | sort - | uniq - | crontab -');
    }

    return true;
  }
}
