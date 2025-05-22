<?php

namespace MrAbdelaziz\BinanceApi\Tests\Feature;

use MrAbdelaziz\BinanceApi\Services\BinanceApiService;
use MrAbdelaziz\BinanceApi\Tests\TestCase;

class ServiceProviderTest extends TestCase
{
    public function test_service_provider_registers_services()
    {
        $this->assertTrue($this->app->bound(BinanceApiService::class));
        $this->assertTrue($this->app->bound('binance-api'));
    }

    public function test_service_provider_resolves_singleton()
    {
        $service1 = $this->app->make(BinanceApiService::class);
        $service2 = $this->app->make(BinanceApiService::class);

        $this->assertSame($service1, $service2);
    }

    public function test_config_is_merged()
    {
        $config = $this->app['config']['binance-api'];

        $this->assertIsArray($config);
        $this->assertArrayHasKey('api_key', $config);
        $this->assertArrayHasKey('api_secret', $config);
        $this->assertArrayHasKey('base_url', $config);
        $this->assertArrayHasKey('testnet', $config);
    }

    public function test_facade_alias_is_registered()
    {
        $this->assertTrue(class_exists(\MrAbdelaziz\BinanceApi\Facades\BinanceApi::class));
    }

    public function test_individual_services_are_bound()
    {
        $this->assertTrue($this->app->bound(\MrAbdelaziz\BinanceApi\Services\AccountService::class));
        $this->assertTrue($this->app->bound(\MrAbdelaziz\BinanceApi\Services\OrderService::class));
        $this->assertTrue($this->app->bound(\MrAbdelaziz\BinanceApi\Services\MarketService::class));
        $this->assertTrue($this->app->bound(\MrAbdelaziz\BinanceApi\Services\PositionService::class));
    }

    public function test_services_resolve_correctly()
    {
        $accountService = $this->app->make(\MrAbdelaziz\BinanceApi\Services\AccountService::class);
        $orderService = $this->app->make(\MrAbdelaziz\BinanceApi\Services\OrderService::class);
        $marketService = $this->app->make(\MrAbdelaziz\BinanceApi\Services\MarketService::class);
        $positionService = $this->app->make(\MrAbdelaziz\BinanceApi\Services\PositionService::class);

        $this->assertInstanceOf(\MrAbdelaziz\BinanceApi\Services\AccountService::class, $accountService);
        $this->assertInstanceOf(\MrAbdelaziz\BinanceApi\Services\OrderService::class, $orderService);
        $this->assertInstanceOf(\MrAbdelaziz\BinanceApi\Services\MarketService::class, $marketService);
        $this->assertInstanceOf(\MrAbdelaziz\BinanceApi\Services\PositionService::class, $positionService);
    }
}
