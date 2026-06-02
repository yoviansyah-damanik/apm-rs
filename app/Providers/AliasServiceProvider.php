<?php

namespace App\Providers;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

class AliasServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $loader = AliasLoader::getInstance();
        $loader->alias('Setting', \App\Helpers\SettingHelper::class);
        $loader->alias('Magic', \App\Helpers\MagicHelper::class);
    }
}
