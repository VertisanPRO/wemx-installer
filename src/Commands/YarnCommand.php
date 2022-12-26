<?php

namespace Billing\Commands\Commands;

use Illuminate\Console\Command;

class YarnCommand extends Command
{

  protected $signature = 'billing:yarn';
  protected $description = 'Will install Node and Yarn for Pterodactyl';

  public function handle()
  {
    $this->yarn();
  }

  private function yarn()
  {
    exec('curl -sL https://deb.nodesource.com/setup_16.x | sudo -E bash -');
    exec('apt install -y nodejs');
    exec('npm i -g yarn');
    exec('cd ' . base_path() . ' && yarn && yarn build:production');
    return $this->info('Node and Yarn installed');
  }
}
