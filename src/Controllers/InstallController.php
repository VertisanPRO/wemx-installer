<?php

namespace Wemx\Installer\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class InstallController 
{
    public function requirements()
    {
        return view('installer::install');
    }

    public function download()
    {
        return view('installer::download');   
    }
}