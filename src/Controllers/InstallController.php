<?php

namespace Wemx\Installer\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class InstallController 
{
    public function index()
    {
        return view('installer::install');
    }

}