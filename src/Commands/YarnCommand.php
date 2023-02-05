<?php

namespace Wemx\Installer\Commands;

use Illuminate\Console\Command;

class YarnCommand extends Command
{

    protected $signature = 'yarn:install
                            {--node= : Node.JS Version to install}';
    protected $description = 'Will install Node and Yarn for Pterodactyl';

    public const NODE_VERSIONS = [
        'v14' => 'Node.JS v14',
        'v15' => 'Node.JS v15',
        'v16' => 'Node.JS v16 (recommended)',
    ];

    public function handle()
    {
        $this->variables['NODE_VERSIONS'] = $this->option('node') ?? $this->choice(
            'What Node.JS Version you want to install?',
            self::NODE_VERSIONS
        );
        $output = array();
        exec("node -v", $output, $return_var);
        if ($return_var === 0) {
            if (!$this->confirm('You already have Node.JS installed, would you like to remove it?')) {
                $this->warn('Node.JS was not removed');

                return;
            }
            $this->handle();
        } else {
            $node_version = substr($this->variables['NODE_VERSIONS'], 1);
            exec('curl -sL https://deb.nodesource.com/setup_' . $node_version . '.x | sudo -E bash -');
            exec('apt install -y nodejs');
            exec('npm i -g yarn');
            exec('yarn && yarn build:production');
            return $this->info('Node.JS with Yarn have been installed and assets built');
        }
    }
}