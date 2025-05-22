<?php

namespace MrAbdelaziz\BinanceApi\Tests;

use MrAbdelaziz\BinanceApi\BinanceApiServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        // Additional setup if needed
    }

    protected function getPackageProviders($app): array
    {
        return [
            BinanceApiServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'BinanceApi' => \MrAbdelaziz\BinanceApi\Facades\BinanceApi::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        // Define test environment variables
        $app['config']->set('binance-api.api_key', 'test_key');
        $app['config']->set('binance-api.api_secret', 'test_secret');
        $app['config']->set('binance-api.testnet', true);
        $app['config']->set('binance-api.base_url', 'https://testnet.binance.vision');
    }
}
