<?php

namespace Wemx\Installer\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAppInstalled
{
    public function handle(Request $request, Closure $next)
    {
        if (!$this->isLaravelSetup()) {
            return redirect('/install/requirements');
        }
        return $next($request);
    }

    private function isLaravelSetup(): bool
    {
        $envPath = base_path('.env');
        if (!file_exists($envPath)) {
            return false;
        }
        $envContents = file_get_contents($envPath);
        if (!str_contains($envContents, 'APP_KEY=base64:')) {
            return false;
        }
        return true;
    }
}