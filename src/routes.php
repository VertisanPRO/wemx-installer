<?php 

use Illuminate\Support\Facades\Route;
use Wemx\Installer\Controllers\InstallController;

Route::controller(InstallController::class)->prefix('install')->group(function () {
    Route::get('/', 'index');
});