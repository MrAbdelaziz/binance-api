<?php

namespace MrAbdelaziz\BinanceApi;

use Illuminate\Support\ServiceProvider;
use MrAbdelaziz\BinanceApi\Services\BinanceApiService;
use MrAbdelaziz\BinanceApi\Services\AccountService;
use MrAbdelaziz\BinanceApi\Services\OrderService;
use MrAbdelaziz\BinanceApi\Services\MarketService;
use MrAbdelaziz\BinanceApi\Services\PositionService;

class BinanceApiServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/binance-api.php',
            'binance-api'
        );

        $this->app->singleton(BinanceApiService::class, function ($app) {
            return new BinanceApiService([
                'api_key' => config('binance-api.api_key'),
                'api_secret' => config('binance-api.api_secret'),
                'base_url' => config('binance-api.base_url'),
                'testnet' => config('binance-api.testnet'),
                'timeout' => config('binance-api.timeout'),
                'rate_limits' => config('binance-api.rate_limits'),
            ]);
        });

        $this->app->bind('binance-api', function ($app) {
            return $app->make(BinanceApiService::class);
        });

        // Register individual services
        $this->app->bind(AccountService::class, function ($app) {
            return new AccountService($app->make(BinanceApiService::class));
        });

        $this->app->bind(OrderService::class, function ($app) {
            return new OrderService($app->make(BinanceApiService::class));
        });

        $this->app->bind(MarketService::class, function ($app) {
            return new MarketService($app->make(BinanceApiService::class));
        });

        $this->app->bind(PositionService::class, function ($app) {
            return new PositionService($app->make(BinanceApiService::class));
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/binance-api.php' => config_path('binance-api.php'),
            ], 'binance-api-config');
        }
    }
}
