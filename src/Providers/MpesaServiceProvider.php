<?php
namespace Mpesa\Providers;

use Illuminate\Support\ServiceProvider;
use Mpesa\Services\C2BService;
use Mpesa\Support\ConfigurationValidator;

class MpesaServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/mpesa.php', 'mpesa');

        $this->app->singleton(C2BService::class, function ($app) {
            // Validate configuration before creating service
            ConfigurationValidator::validate();
            return new C2BService();
        });
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/mpesa.php' => $this->app->configPath('mpesa.php'),
            ], 'config');
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
    }
}
