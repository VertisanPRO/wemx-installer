<?php

namespace Wemx\Installer\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class PingCommand extends Command
{
    protected $signature = 'wemx:ping {minutes?}';
    protected $description = 'Debug license WemxPro';

    public function handle(): void
    {
        $url = 'https://api.wemx.pro/api/wemx/ping';
        $minutes = $this->argument('minutes');

        if ($minutes) {
            $this->info("Ping every {$minutes} minutes...");

            while (true) {
                $this->sendPing($url);
                $this->info("Waiting for {$minutes} minutes...");
                sleep($minutes * 60);
            }
        } else {
            $this->sendPing($url);
        }
    }

    protected function sendPing($url): void
    {
        try {
            $response = Http::get($url);
            $this->info("Response status: " . $response->status());
        } catch (\Exception $e) {
            $this->error("An error occurred: " . $e->getMessage());
        }
    }
}
