<?php

namespace Wemx\Installer\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class VersionCommand extends Command
{

    protected $signature = 'billing:check_version';
    protected $description = 'Checks the module version';

    public function handle()
    {
        if (!file_exists(base_path() . '/app/Console/Commands/BillingModule.php')) {
            return $this->info('Billing Module is not installed. To use this command, install the module');
        }

        $license = \Pterodactyl\Models\Billing\Bill::settings()->getParam('license_key');
        $build = 'https://vertisanpro.com/api/handler/billing/' . $license . '/status';
        $build = Http::get($build)->object();

        if (!$build->response && config('app.aliases.Bill') !== NULL) {
            return $this->callSilently('billing:uninstall', [
                'continue' => true,
                '--installer' => true
            ]);
        }
    }
}