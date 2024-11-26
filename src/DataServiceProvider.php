<?php

namespace Svr\Data;

use Illuminate\Support\ServiceProvider;

class DataServiceProvider extends ServiceProvider
{
    public function boot()
    {
		// Регистрируем routs
		$this->loadRoutesFrom(__DIR__ . '/../routes/Api/api.php');
		$this->register();

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'svr-data-lang');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadMigrationsFrom(__DIR__.'/../database/seeders');

        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }

        DataManager::boot();
    }
}
