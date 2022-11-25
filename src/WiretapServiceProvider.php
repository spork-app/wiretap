<?php

namespace Spork\Wiretap;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class WiretapServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/spork.php', 'spork.wiretap');
        Route::middleware('web')->group(__DIR__.'/../routes/web.php');
    }
}
