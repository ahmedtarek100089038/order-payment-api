<?php

namespace App\Providers;

use App\PaymentGateways\PaymentGatewayManager;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentGatewayManager::class, function ($app) {
            return new PaymentGatewayManager();
        });
    }

    public function boot(): void
    {
        //
    }
}